<?php
//后台相关路由
use think\facade\Route;
Route::get('mymanage/','mymanage/index/index');//后台首页
Route::get('manageLogin','mymanage/login/index');//后台登录
Route::get('adminyzm', 'mymanage/login/yzm');//登录验证码
Route::get('adminout','mymanage/login/loginout');//退出登录
