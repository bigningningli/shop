<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27 0027
 * Time: 下午 4:11
 */

namespace app\admin\controller;
use app\admin\controller\Common;
use think\Cookie;
class Region extends Common
{

    public function init(){

        
    }
    public function index(){
        if(!$this->auth->check('system/see',Cookie::get("uid"))){
            return view('./error/authority');
        }
        $this->assign([
            "classlist"=>$this->regionarr,
        ]);
        return view();
    }
    public function add(){
        if(!$this->auth->check('system/add',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()){

            $data=input("post.");

            $level=$this->modelregion->where('id',input("pid"))->column("level");
            if($level===false){
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }

            if(count($level)){
                $data=array_merge($data,["level"=>$level[0]+1]);
            }else{
                $data=array_merge($data,["level"=>1]);
            }


            if($this->modelregion->allowField(true)->save(
                $data
            )){
                return json_encode(["msg"=>"操作成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }

        }
        $class=$this->regionarr;

        foreach ($class as $k=>$v){
            $class[$k]["name"]="|-".str_repeat("--",($v["level"]-1)*2).$v["name"];
        }


        $this->assign([
            "class"=>$class,
        ]);
        return view();
    }
    public function edit(){
        if(!$this->auth->check('system/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()){

            $data=input("post.");

            $id=["id"=>input('id')];

            unset($data["id"]);

            $level=$this->modelregion->where('id',input("pid"))->column("level");
            if($level===false){
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }

            if(count($level)){
                $data=array_merge($data,["level"=>$level[0]+1]);
            }else{
                $data=array_merge($data,["level"=>1]);
            }


            if($this->modelregion->allowField(true)->save(
                $data,$id
            )){
                return json_encode(["msg"=>"操作成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelregion->getError(),"status"=>500]);
            }

        }
        $result = $this->modelregion->where("id",input('get.')["id"])->select()->toArray()[0];

        $class=$this->regionarr;

        foreach ($class as $k=>$v){
            $class[$k]["name"]="|-".str_repeat("--",($v["level"]-1)*2).$v["name"];
        }

        $this->assign([
            "class"=>$class,
            "result"=>$result
        ]);
        return view();
    }

    public function delete(){
        if(!$this->auth->check('system/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
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