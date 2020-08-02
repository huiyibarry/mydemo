<?php
namespace app\mymanage\controller;
use app\mymanage\controller\Base;
use think\db;
class Config extends Base{
    /* 
    网站信息
    */
    public function index(){
        $this->assign('title',"系统信息");
        return $this->fetch();
    }

    public function doadd(){
        
    }
}