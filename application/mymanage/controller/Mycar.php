<?php
/**
 * 记录汽车加油记录
 */
namespace app\mymanage\controller;
use app\mymanage\controller\Base;
use think\Db; 
use think\facade\Request;
class Mycar extends Base{

    public function index(){
        return view('',['title'=>'汽车加油记录']);
    }

    public function add(){
        if($this->request->isPost()){
            $param = $this->request->param();
            $param['create_time'] = time();
            $id = Db::name('mycar')->insert($param);
            if($id){
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }
        }else{
            return view('',['title'=>'添加汽车加油记录']);
        }
    }

    public function edit(){
        if($this->request->isPost()){
            $param = $this->request->param();
            $id = $param['id'];
            unset($param['id']);
            $res = Db::name('mycar')->where('id',$id)->update($param);
            if($res){
                $this->success("修改成功");
            }else{
                $this->error("修改失败");
            }
        }else{
            $id=Request::param('id');
            $info=Db::name('mycar')->find($id);
            return view('',['title'=>'汽车加油记录修改','info'=>$info]);
        }
       
    }

    public function del(){
        $id=Request::param('id');
        $res = Db::name('mycar')->where('id',$id)->delete();
        if($res){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }

    public function get_list(){
        $param = Request::param();
        $rows = empty($param['limit'])?10:$param['limit'];
        $where=[];
        $list = Db::name('mycar')->where($where)->paginate($rows,false,$param);
        $this->layer_return($list);
    }
}