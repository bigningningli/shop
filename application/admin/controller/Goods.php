<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1 0001
 * Time: 上午 11:19
 */

namespace app\admin\controller;
use app\admin\model\Goods as modelgoods;
use app\admin\controller\Common;
use think\Cookie;
class Goods extends Common
{
    public function init(){
        
    }
    public function index(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modelgoods->select()->toArray();

        foreach ($result as $key=>$value){
                $result[$key]["images"]=explode(",",$value["images"])[0];
        }

        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
            "webuploader"=>$this->webuploader
        ]);

        return view();
    }
    public function add(){
        if(!$this->auth->check(request()->controller().'/add',Cookie::get('uid'))){
            return view('./error/authority');
        }
        if(request()->isPost()){

            $data=input('post.');

            if(!isset($data["status"]))
                $data=array_merge($data,["status"=>0]);


            if(!isset($data["is_home_recommended"]))
                $data=array_merge($data,["is_home_recommended"=>0]);

            if($this->modelgoods->allowField(true)->validate(true)->save($data)){
                return json_encode(["msg"=>"添加成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelgoods->getError(),"status"=>500]);
            }
        }

        $this->assign([
            "province"=>$this->regionclass,
            "category"=>$this->goodscategoryclass
        ]);
        return view();
    }
    public function edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modelgoods->where('id',input('id'))->select()->toArray()[0];
        if(request()->isPost()){

            $data=input('post.');

            $id=array("id"=>$data["id"]);
            unset($data["id"]);
            if(!isset($data["status"]))
                $data=array_merge($data,["status"=>0]);

            if(!isset($data["is_home_recommended"]))
                $data=array_merge($data,["is_home_recommended"=>0]);

            $this->modelgoods->startTrans();

            if($this->modelgoods->allowField(true)->validate(true)->save($data,$id)!==false){

                if($this->imgdel($result["images"],$data["images"])){
                    $this->modelgoods->commit();
                    return json_encode(["msg"=>"修改成功","status"=>200]);
                }else{
                    $this->modelgoods->rollback();
                    return json_encode(["msg"=>"删除失败","status"=>200]);
                }
            }else{
                $this->modelgoods->rollback();

                return json_encode(["msg"=>$this->modelgoods->getError(),"status"=>500]);
            }


        }

        $imagesarr=explode(",",$result["images"]);
        array_pop($imagesarr);

        $this->assign([
            "province"=>$this->regionclass,
            "category"=>$this->goodscategoryclass,
            "unitarr"=>$this->unitarr,
            "result"=>$result,
            "webuploader"=>$this->webuploader,
            "imagesarr"=>$imagesarr,
            "margin"=>-105,
        ]);

       return view();
     }
     public function status(){
         if(!$this->auth->check(request()->controller().'/edit',Cookie::get('uid'))){
             return json_encode(["msg"=>"权限不足","status"=>403]);
         }
         if(request()->isPost()){
             if($this->modelgoods->where('id',input('id'))->update(['status'=>input('data')])===false){
                 return json_encode(["msg"=>$this->modelgoods->getError(),"status"=>500]);
             }else{
                 return json_encode(["status"=>200]);
             }

         }
     }
    public function delete(){


        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()) {

            if(modelgoods::destroy(function ($query){$query->where("id in (".input('id').")");})){

                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{

                return json_encode(["msg"=>$this->modelgoods->getError(),"status"=>500]);
            }


        }

    }
    public function recovery(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modelgoods->onlyTrashed()->select()->toArray();

        foreach ($result as $key=>$value){
            $result[$key]["images"]=explode(",",$value["images"])[0];
        }

        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
            "webuploader"=>$this->webuploader
        ]);

        return view();
    }
    public function redelete(){


        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()) {
            $this->modelgoods->startTrans();
            $arr = $this->modelgoods->where("id in (" . input('id') . ")")->column('images');
            foreach ($arr as $value) {
                $marr=explode(",",$value);
                array_pop($marr);
                foreach ($marr as $v){
                    if (!unlink($_SERVER["DOCUMENT_ROOT"].$this->webuploader . $v)) {
                        $this->modelgoods->rollback();
                        return json_encode(["msg" => "删除失败", "status" => 500]);
                    }
                }

            }

            if(modelgoods::destroy(function ($query){$query->where("id in (".input('id').")");},true)){
                $this->modelgoods->commit();
                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                $this->modelgoods->rollback();
                return json_encode(["msg"=>$this->modelgoods->getError(),"status"=>500]);
            }


        }

    }
    public function start(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get('uid'))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if($this->modelgoods->onlyTrashed()->where('id',input('id'))->update(["delete_time"=>null])){
            return json_encode(["msg"=>"恢复成功","status"=>200]);
        }else{
            return json_encode(["msg"=>$this->modelgoods->getError(),"status"=>500]);
        }
    }

}