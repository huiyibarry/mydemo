<?php
namespace app\mymanage\controller;
use think\Db;
use think\Controller;
use think\captcha\Captcha;
use think\facade\Request;
use think\facade\Session;
class Login extends Controller{

    public function index(){
        if(Session::has('admin')){
            $this->redirect('/mymanage');
        }
        return $this->fetch();
    }

    public function dologin(){
        if($this->request->isPost()){
            $param = $this->request->param();
            $result = $this->validate($param,'app\mymanage\validate\Login');
    
            if (true !== $result) {
                $token = $this->request->token('__token__', 'sha1');
                return $this->error($result,'',$token);
            }else{
                $find=Db::name('admin')->where(['username'=>$param['username']])->find();
                if(!$find){
                    $this->error("账号不存在");
                }
                if(md5(md5($param['password']).$find['pass_str']) != $find['pass']){
                    return $this->error("密码错误");
                }
                $ip=GetIp();
                $log_str=GetIpLookup($ip);
                Db::name('admin')->where('id',$find['id'])->update(['login_time'=>time(),'login_ip'=>$ip]);
                Db::name('admin_login')->insert(['admin_id'=>$find['id'],'login_ip'=>$ip,'login_time'=>time(),'login_str'=>$log_str]);
                Session::set('admin',$find);
                return $this->success("登录成功");
            }
        }
    }

    public function loginout(){
        Session::delete('admin');
        $this->redirect('/manageLogin');
    }

    public function yzm(){
        $config =    [
            // 验证码字体大小
            'fontSize'    =>    20,    
            // 验证码位数
            'length'      =>    4,   
            // 关闭验证码杂点
            'useNoise'    =>    false, 
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

}