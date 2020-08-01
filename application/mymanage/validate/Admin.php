<?php
namespace app\mymanage\validate;
use think\Validate;
class Admin extends Validate{
    protected $rule=[
        'username'=>'require|length:5,10',
        'pass' =>'require',
        'repass'=>'require',
    ];
    protected $message=[
        'username.require' =>'请输入管理员名称',
        'username.length' =>'管理员名称在5到10之间',
        'pass' =>'请输入密码',
        'repass'=>'请再次输入密码',
    ];

}