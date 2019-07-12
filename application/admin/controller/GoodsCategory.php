<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/31 0031
 * Time: 下午 4:22
 */

namespace app\admin\controller;
use think\Cookie;
use app\admin\controller\Common;
class GoodsCategory extends Common
{
    public function init(){

    }
    public function index(){
        if(!$this->auth->check('goods/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $this->assign([
            "class"=>$this->goodscategoryarr,
        ]);
        return view();
    }
    public function add(){
        if(!$this->auth->check('goods/add',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $class=$this->goodscategoryarr;

        foreach ($class as $k=>$v){
            $class[$k]["name"]="|-".str_repeat("--",($v["level"]-1)*2).$v["name"];
        }

        if(request()->isPost()){
            $data=input('post.');
            if(input('pid')!=0){
               $data=array_merge($data,["level"=>$this->modelregion->where('id',input('pid'))->column('level')]);
             }else{
                $data=array_merge($data,["level"=>0]);
            }
            $data["level"]+=1;
            if($this->modelregion->save(
                $data
            )){
                return json_encode(["msg"=>"添加成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }
        }

        $this->assign([
            "class"=>$class,
        ]);



        return view();
    }
    public function edit(){
        if(!$this->auth->check('goods/edit',Cookie::get('uid'))){
            return view('./error/authority');
        }
        if(request()->isPost()){
            $data=input('post.');
            $id=["id"=>$data["id"]];
            unset($data["id"]);
            if($this->modelregion->save($data,$id)===false){
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }else{
                return json_encode(["msg"=>"修改成功","status"=>200]);
            }
        }


        $class=$this->goodscategoryarr;

        foreach ($class as $k=>$v){
            $class[$k]["name"]="|-".str_repeat("--",($v["level"]-1)*2).$v["name"];
        }

        $result=$this->modelregion->where('id',input('id'))->select()->toArray()[0];
        $this->assign([
            "class"=>$class,
            "result"=>$result
        ]);
        return view();
    }
    public function delete(){
        if(!$this->auth->check('goods/add',Cookie::get('uid'))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){

            $result=$this->modelregion->where("pid",input('id'))->select()->toArray();
            if(count($result)>0){
                return json_encode(["msg"=>"请先删除下级分类","status"=>500]);
            }else{
                if ($result===false){
                    return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
                }
            }
            if($this->modelregion->where("id",input('id'))->delete()){

                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }
        }
    }

}