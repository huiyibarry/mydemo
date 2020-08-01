<?php
namespace app\mymanage\controller;
use think\Controller;
use think\facade\Session;
class Base extends Controller{
    public function initialize(){
        if(!Session::has('admin')){
            $this->redirect('/manageLogin');
        }
    }
    // layui 数据返回
    public function layer_return($data,$code=0,$msg='',$httpCode = 200,$header=[],$options=[]){
        $res['code'] = $code;
        $res['msg'] = $msg;    
        if (is_object($data)) {    
            $data = $data->toArray();    
        }  
        if (!empty($data['total'])) {    
            $res['count'] = $data['total'];    
        } else {    
            $res['count'] = 0;    
        }    
        $res['data'] = $data['data'];
        $response = \think\Response::create($res, "json", $httpCode, $header, $options);
        throw new \think\exception\HttpResponseException($response);

    }
}