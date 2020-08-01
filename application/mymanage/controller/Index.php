<?php
namespace app\mymanage\controller;
use app\mymanage\controller\Base;
use think\Db;
use think\facade\Cache;
use think\facade\Env;
class Index extends Base{
    public function index(){  
        $this->assign('title',"后台");    
        $nav=$this->get_nav();
        $this->assign('nav',$nav);
        return $this->fetch();
    }

    public function main()
    { 
       $this->assign('title',"后台");  
       return $this->fetch();
    }

    public function get_nav(){       
        if(Cache::get('admin_nav')){
            return Cache::get('admin_nav');
        }else{
            $list=Db::name('system')->where('parentid',0)->where('status',1)->order('oid desc')->select();
            foreach($list as &$row){                
                $row['list']=Db::name('system')->where('parentid',$row['id'])->where('status',1)->order('oid desc')->select();
            }
            unset($row);
            Cache::set('admin_nav',$list,3600);
            return $list;
        }       
    }

    public function clear_cache(){
        $CACHE_PATH = Env::get('runtime_path') . 'cache/';
        $TEMP_PATH = Env::get('runtime_path'). 'temp/';
        $LOG_PATH = Env::get('runtime_path'). 'log/';
        if (delete_dir_file($CACHE_PATH) && delete_dir_file($TEMP_PATH) && delete_dir_file($LOG_PATH)) {
            Cache::clear(); 
            $this->success("清除缓存成功");
        } else {
            $this->error("清除缓存失败");
        }
    }

}