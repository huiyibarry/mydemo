<?php
namespace app\common\wechat;
use think\Controller;
use think\Db;
class Api extends Controller{
    protected $AppID = "wx7431a9a4ff1524c7";//微信APPID
    protected $AppSecret = "148a4759f4efbf3dca472b3daf4f48cd";//微信开发者密码

    public function get_access_token(){
        $access_token = cache('access_token');
        if(!empty($access_token)) return $access_token;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->AppID . "&secret=" . $this->AppSecret;
        $result = self::https_request($url);
        $jsoninfo = json_decode($result, true);
        $access_token = $jsoninfo["access_token"];
        //更新数据
        cache('access_token', $access_token, 7200);
        return $access_token;
    }

    public function qrcode_img($option = array()) {
        $option = array('type'=>'login','params'=>1000);
        $config = array('type'=>'','width'=>120,'height'=>120,'params'=>0);
        $config = array_merge($config,$option);
        $access_token = self::get_access_token();
        $data['type'] = $config['type'];		
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
        $post_data = '{"expire_seconds": 1800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "' . http_build_query($data) . '"}}}';
        $result = self::https_request($url, $post_data);
        $result_arr = json_decode($result, true);
        if ($result_arr['errcode'] == 40001) {
            self::get_access_token();
            self::qrcode_img($config);
        } else {
            $ticket = urlencode($result_arr["ticket"]);
            $html = '<img width=' . $config['width'] . ' height=' . $config['height'] . ' src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket . '">';
            return $html;
        }
    }

    public function https_request($url, $data = null) {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        } else {
            return false;
        }
    }


}