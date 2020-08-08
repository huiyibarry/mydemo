<?php
namespace app\index\controller;
use think\Controller;
use app\common\wechat\Api;
class Index extends Controller
{
    public function index()
    {
       return $this->fetch();
    }

    public function hello($name = 'ThinkPHP5')
    {
        $api = new Api();
        $html = $api->qrcode_img();
        return $html;
    }
}
