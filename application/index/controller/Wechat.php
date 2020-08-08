<?php
namespace app\index\controller;
use think\Controller;
use think\facade\Log;
use think\Db;
use app\common\wechat\WXBizMsgCrypt;
class Wechat extends Controller{
    protected $AppID = "wx7431a9a4ff1524c7";//微信APPID
    protected $AppSecret = "148a4759f4efbf3dca472b3daf4f48cd";//微信开发者密码
    protected $Token = "lfg0613cn";
    protected $EncodingAESKey = "SkxFCFL3fv3OijyaRn17bergQB8B7VRicJIr5Qs4kfm";
    protected $content = "";
    protected $tuling_url = "http://openapi.tuling123.com/openapi/api/v2";
    public function index() {
        $echoStr = input('get.echostr','','trim');
        $echoStr && $this->_valid($echoStr);
        $this->_responseMsg();
    }

    /**
     * [_valid 微信接入认证]
     */
    protected function _valid($echoStr) {
        if ($this->checkSignature()) exit($echoStr);
        exit('false');       
    }

    /**
     * [checkSignature 验名认证]
     */
    protected function checkSignature() {
        $signature = input('get.signature','','trim');  //微信加密签名
        $timestamp = input('get.timestamp','','trim');  //时间戳
        $nonce = input('get.nonce','','trim');	      //随机数
        $tmpArr = array($this->Token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }        
    }
    //接收消息
    protected function _responseMsg(){
        //用户发送消息接收，接收格式是XML    
        $postStr = file_get_contents('php://input');      
        if(!empty($postStr)){ 
            $pc = new WXBizMsgCrypt($this->Token, $this->EncodingAESKey, $this->AppID);
            $timestamp = input('get.timestamp','','trim');
            $nonce = input('get.nonce','','trim');
            $msg_signature = input('get.msg_signature','','trim');
            $encrypt_type = 'aes' == input('get.encrypt_type', '', 'trim') ? 'aes' : 'raw';           
            //解密
            if($encrypt_type == "aes"){               
                $decryptMsg = '';
                $errCode = $pc->decryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg);
                if ($errCode != 0) {
                    echo "";
                    exit;
                }
                $postStr = $decryptMsg;
            }            
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //可以开始使用这个XML对象
            /* $fromUsername = $postObj->FromUserName;//发送方帐号（一个OpenID）
            $toUsername = $postObj->ToUserName;//开发者微信号
            $keyword = trim($postObj->Content);//发送的文本消息内容 */
            //使用了图灵机器人自动回复 
            
            $msgType=$postObj->MsgType;//获取接收消息的类型
            /* Log::write('消息类型是'.$msgType);
            if($msgType == "event"){
                $result = $this->receiveEvent($postObj); 
                if ($encrypt_type == 'aes') {
                    $encryptMsg = ''; //加密后的密文
                    $errCode = $pc->encryptMsg($result, $timestamp, $nonce, $encryptMsg);
                    if ($errCode != 0) {
                        echo "";
                        exit;
                    }
                    $result = $encryptMsg;
                }
                echo $result;
            }
            return true; */
            switch($msgType){
                case "event":
                   $result = $this->receiveEvent($postObj); 
                   break;
                case "text":
                    $result = $this->receiveText($postObj); 
                   break;
                default:
                   $result = "unknown msg type: " . $msgType;
                   break;   
            }
            if ($encrypt_type == 'aes') {
                $encryptMsg = ''; //加密后的密文
                $errCode = $pc->encryptMsg($result, $timestamp, $nonce, $encryptMsg);
                if ($errCode != 0) {
                    echo "";
                    exit;
                }
                $result = $encryptMsg;
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    protected function receiveEvent($object){
        //subscribe(订阅)、unsubscribe(取消订阅)
        switch($object->Event){
            case "subscribe":
                if ($object->EventKey) {//用户未关注时，进行关注后的事件推送
                    $this->actionScan($object);
                } else {
                    $this->add_fans($object->FromUserName);       
                    $this->content = "欢迎关注";
                } 
            break;
            case "unsubscribe":
                Db::name('wx_fans')->where('openid',addslashes($object->FromUserName))->delete();
            break;   
            default:
            $this->content = "欢迎关注";
            break;
        }

        if (is_array($this->content)) {
            if (isset($this->content[0])) {
                $result = $this->transmitNews($object, $this->content);
            }
        } else {
            $result = $this->transmitText($object, $this->content);
        }
        return $result;

    }
    //接收文本消息
    protected function receiveText($object){
        $keyword = trim($object->Content);
        $keyword = addslashes($keyword);
        $this->content = "暂停回复";
        $this->add_fans($object->FromUserName);       
        $result = $this->transmitText($object, $this->content);
        return $result;
    }
    //扫码
    protected function actionScan($object){
        $event_key = stripos($object->EventKey, 'qrscene_') === false ? $object->EventKey : trim($object->EventKey, 'qrscene_');
        parse_str($event_key,$event);
        if(isset($event['type'])){
            switch($event['type']){
                case "login":
                $is_bind = $this->user_is_bind($object->FromUserName);
                if($is_bind){
                    $this->content = "点击登录";
                }else{
                    $this->content = "未绑定";
                }
                break;    
                default:
                $this->content = "欢迎关注";
                $this->add_fans($object->FromUserName);       
            }
        }else{
            $this->content = "欢迎关注";
        }
        $this->add_fans($object->FromUserName);       
       
    }

    //回复文本消息
    private function transmitText($object, $content) {
        $xmlTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>';
        $content = $content;
        $result = sprintf($xmlTpl, addslashes($object->FromUserName), $object->ToUserName, time(), $content);
        return $result;
    }
    
    //回复图文消息
    private function transmitNews($object, $newsArray) {
        if (!is_array($newsArray)) return;
        $itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
        $item_str = "";
        foreach ($newsArray as $item) {
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount>
            <Articles>
            $item_str</Articles>
            </xml>";
        $result = sprintf($xmlTpl, addslashes($object->FromUserName), $object->ToUserName, time(), count($newsArray));
        return $result;
    }
    
    public function add_fans($openid){
        $find = Db::name('wx_fans')->where('openid',$openid)->find();
        if(empty($find)){
            Db::name('wx_fans')->insert(['openid'=>$openid,'create_time'=>time()]);
        }
    }

    public function user_is_bind($openid){
        $find = Db::name('wx_fans')->where('openid',$openid)->find();
        if(empty($find)){
            return false;
        }else{
            if(isset($find['uid'])){
                return true;
            }else{
                return false;
            }
        }
    }
    //图灵机器人回复
    public function tuling(){

    }

}