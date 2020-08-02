<?php
namespace app\mymanage\controller;
use app\mymanage\controller\Base;
use think\facade\Request;
use think\db;
class System extends Base{

    public function index()
    {  
       $list=Db::name('system')->where('parentid',0)->order('oid desc')->paginate(10)->each(function($item){           
            $sonlist=Db::name('system')->where('parentid',$item['id'])->order('oid desc')->select();
            if($sonlist){
                $item['has_list']=2;
            }else{
                $item['has_list']=1;
                $sonlist=[];
            }
            $item['sonlist']=$sonlist;
            return $item;
       })?:[];  
     
       // 模板变量赋值
       $this->assign('list', $list);
       $this->assign('title', "系统设置");    
       // 渲染模板输出
       return view();
    }

    public function add(){
        $parentid= Request::param('parentid',0);
        $parent_list=Db::name('system')->where('parentid',0)->field('id,name')->select();
        $this->assign('parent_list',$parent_list);
        $this->assign('parentid',$parentid);
        $this->assign('title',"系统添加");
        return view();
    }

    public function doadd(){
        $param=Request::param();
        $add['name'] = $param['name'];
        $add['path'] = $param['path'];
        $add['status'] = $param['status'];
        $add['oid'] = Request::param('oid',0);
        $add['fonts'] = Request::param('fonts/s','');
        $add['parentid'] = $param['parentid'];
        if(!$add['name']){
            $this->error("请输入系统名称");
        }
        if(!$add['path']){
            $this->error("请输入系统路径");
        }
        $id=Db::name('system')->insert($add);
        if($id){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
    }
    
    public function edit(){
        $id=Request::param('id');
        $info=Db::name('system')->find($id);
        $parent_list=Db::name('system')->where('parentid',0)->field('id,name')->select();
        $this->assign('parent_list',$parent_list);
        $this->assign('info',$info);
        $this->assign('title',"系统修改");
        return view();
    }

    public function doedit()
    {
        $param=Request::param();
        $add['name'] = $param['name'];
        $add['path'] = $param['path'];
        $add['status'] = $param['status'];
        $add['oid'] = Request::param('oid',0);
        $add['fonts'] = Request::param('fonts/s','');
        $add['parentid'] = $param['parentid'];
        $id=$param['id'];
        if(!$add['name']){
            $this->error("请输入系统名称");
        }
        if(!$add['path']){
            $this->error("请输入系统路径");
        }

        $res=Db::name('system')->where('id',$id)->update($add);
        if($res){
            $this->success("修改成功");
        }else{
            $this->error("修改失败");
        }
    }
    //字体
    public function unicode()
    {
        return view('',['title'=>"字体图标"]);
    }
}