<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/20 0020
 * Time: 下午 8:30
 */

namespace app\admin\controller;
use think\Cookie;
use app\admin\controller\Common;
class Links extends Common
{
    public function init(){

    }
    public function index(){
        if(!$this->auth->check('layout/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modellinks->select()->toArray();


        $this->assign([
            "columnlist"=>$result,
            "sum"=>count($result)
        ]);
        return view();
    }
    public function add(){
        if(!$this->auth->check('layout/add',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $sort=$this->modellinks->order('sort')->select()->toArray();

        if(request()->isPost()){
            $data=input('post.');
            if($this->modellinks->where('sort','>',$data["sort"])->setInc('sort',1)===false) {
                return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
            }
            $data["sort"]+=1;
            if($this->modellinks->allowField(true)->validate(true)->save(
                $data
            )){
                return json(["msg"=>"添加成功","status"=>200]);
            }else{
                return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
            };

        }
        $this->assign([
            'sort'=>$sort,
        ]);
        return view();
    }
    public function edit(){

        if(!$this->auth->check('layout/edit',Cookie::get('uid'))){
            return view('./error/authority');
        }
        if(request()->isPost()){
            $stemp=$this->modellinks->where('id',input('id'))->select()->toArray()[0]["sort"];
            $data=input('post.');
            $id=array("id"=>input('id'));
            unset($data["id"]);
            $this->modellinks->startTrans();
            if($stemp<input('sort')){
                if($this->modellinks->where('sort',"<=",input('sort'))->where('sort',">",$stemp)->setDec('sort')===false)
                {
                    $this->modellinks->rollback();
                    return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
                }
            }
            if($stemp>=input('sort')) {
                if ($this->modellinks->where('sort', ">", input('sort'))->where('sort', "<=", $stemp)->setInc('sort') === false)
                {

                    $this->modellinks->rollback();
                    return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
                }
                $data["sort"]+=1;
            }



            if($this->modellinks->allowField(true)->validate(true)->save(
                $data,$id
           )===false){
                $this->modellinks->rollback();
                return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
            }else{
                $this->modellinks->commit();
                return json(["msg"=>"修改成功","status"=>200]);
            }
        }else{
            $sort=$this->modellinks->order('sort')->where('id','<>',input('id'))->select()->toArray();
            $sortselect=$this->modellinks->order('sort','desc')->where('sort','<',input('sort'))->column('id');
            if(count($sortselect)){
                $sortselect=$sortselect[0];
            }else{
                $sortselect=-1;
            }
            $result=$this->modellinks->where('id',input('id'))->select()->toArray()[0];
        }

        $this->assign([
            "sort"=>$sort,
            "ss"=>$sortselect,
            "result"=>$result
        ]);

        return view();
    }
    public function status(){
        if(!$this->auth->check('layout/edit',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            if($this->modellinks->where('id',input('id'))->update(['status'=>input('data')])===false){
                return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
            }else{
                return json(["status"=>200]);
            }

        }
    }
    public function delete(){
        if(!$this->auth->check('layout/delete',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            $d=explode(',',input('id'));
            foreach ($d as $v){
                $this->modellinks->where('sort','>',$this->modellinks->where('id',$v)->column('sort')[0])->setDec('sort',1);

            }
            if($this->modellinks->where("id in (".input('id').")")->delete()){
                return json(["msg"=>"删除成功","status"=>200]);
            }else{
                return json(["msg"=>$this->modellinks->getError(),"status"=>500]);
            }
        }
    }
}