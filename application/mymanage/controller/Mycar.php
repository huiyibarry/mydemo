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

    public function get_list(){
        $param = Request::param();
        $rows = empty($param['limit'])?10:$param['limit'];
        $where=[];
        $list = Db::name('mycar')->where($where)->paginate($rows,false,$param);
        $this->layer_return($list);
    }
}