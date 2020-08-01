<?php
namespace app\mymanage\controller;
use app\mymanage\controller\Base;
use think\Db; 
use think\facade\Request;
class Admin extends Base{
    public function index()
    {
        return view('',['title'=>'管理员列表']);
    }

    public function add(){        
        if($this->request->isPost()){
            $param = $this->request->param();
            $result = $this->validate($param,'app\mymanage\validate\Admin');
            if ($result !== true) {
                return $this->error($result);
            }else{
                if($param['repass'] !=$param['pass']){
                    return $this->error("两次密码不一致");
                }
                $has_name = Db::name('admin')->where('username',$param['username'])->find();
                if(isset($has_name)){
                    return $this->error("当前用户名已存在");
                }
                $pass_str=randstr();
                $add['username']=$param['username'];
                $add['pass']=md5(md5($param['pass']).$pass_str);
                $add['create_time']=time();
                $add['role']=$param['role'];
                $add['pass_str']=$pass_str;
                $res=Db::name('admin')->insert($add);
                if($res){
                    return $this->success("添加成功");
                }else{
                    return $this->error("添加失败");
                }
               
            }
        }else{
            $title = "添加管理员";
            return view('',['title'=>$title]);
        }
        
    }

    public function edit()
    {
       if($this->request->isPost()){
        $param = $this->request->param();
        $id=$param['id'];
        $username=$param['username'];
        $pass=$param['pass'];
        $role=$param['role'];
        $repass=$param['repass'];
        $result = $this->validate($param,'app\admin\validate\Admin');
        if ($result !== true) {
            return $this->error($result);
        }
        if($repass !=$pass){
            return $this->error("两次密码不一致");
        }
        $data['username']=$username;
        $data['role']=$role;
        $pass_str=randstr();
        $data['pass']=md5(md5($pass).$pass_str);
        $data['pass_str']=$pass_str;
        $res= Db::name('admin')->where('id',$id)->update($data);
       
        if($res){
            return $this->success("修改成功");
        }else{
            return $this->error("修改失败");
        }
       }else{
        $id=Request::param('id');
        $info=Db::name('admin')->find($id);
        return view('',['title'=>'管理员修改','info'=>$info]);
       } 
      
    }

    public function get_adminlist(){
        $param = Request::param();
        $rows = empty($param['limit'])?10:$param['limit'];
        $where=[];
        if($param['username']){
            $where[]=['username','like','%'.$param['username'].'%'];
        }
        $list=Db::name('admin')->where($where)->paginate($rows,false,$param)->each(function($item){
            switch($item['role']){
                case 1:
                    $item['role']="超级管理员";
                break;
                case 2:
                    $item['role']="管理员";
                break;
            }
            return $item;
        })?:[]; 
        $this->layer_return($list);
    }



}