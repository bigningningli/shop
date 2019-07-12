<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/28 0028
 * Time: 下午 3:47
 */

namespace app\admin\controller;
use app\admin\controller\Common;
use think\Cookie;
class User extends Common
{
    public function init(){

    }
    public function index(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get("uid"))){
            return view('./error/authority');
        }
        $result=$this->modeluser->field('id,name,sex,phone,email,create_time,status')->select();
        $this->assign([
            "result"=>$result
        ]);
        return view();
    }
    public function add(){
        if(!$this->auth->check(request()->controller().'/add',Cookie::get("uid"))){
            return view('./error/authority');
        }
        $region=$this->modelregion->where('level',1)->field('name')->select()->toArray();
        if(request()->isPost()){
            $data=input('post.');

            if(!$this->validateuser->check($data)){
                return json_encode(["msg"=>$this->validateuser->getError(),"status"=>500]);
            }

            $data["password"]=md5($data["password"]);

            unset($data["password_confirm"]);
           if($this->modeluser->save(
                $data
           )){
                return json_encode(["msg"=>"添加成功","status"=>200]);
           }else{
               return json_encode(["msg"=>$this->modeluser->getError(),"status"=>500]);
             }
        }
       $this->assign([
           "region"=>$region
       ]);
        return view();
    }
    public function status(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            if($this->modeluser->where('id',input('id'))->update(['status'=>input('data')])===false){
                return json_encode(["msg"=>$this->modeluser->getError(),"status"=>500]);
            }else{
                return json_encode(["status"=>200]);
            }

        }
    }
    public function edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }

        if(request()->isPost()){

            $data=input('post.');

            if(!$this->validateuser->check($data)){
                return json_encode(["msg"=>$this->validateuser->getError(),"status"=>500]);
            }

            if($data["password"]==$data["password_confirm"]&&$data["password"]==""){
                unset($data["password"]);

            }else{
                $data["password"]=md5($data["password"]);
            }
            unset($data["password_confirm"]);
            $id=["id"=>$data["id"]];

            unset($data["id"]);

            if($this->modeluser->allowField(true)->validate(true)->save(
                    $data,$id
                )===false){

                return json_encode(["msg"=>$this->modeluser->getError(),"status"=>500]);
            }else{

                return json_encode(["msg"=>"修改成功","status"=>200]);
            }
        }else{

            $result=$this->modeluser->where('id',input('id'))->select()->toArray()[0];
        }
        $region=$this->modelregion->where('level',1)->field('name')->select()->toArray();
        $this->assign([
            "region"=>$region,
            "result"=>$result
        ]);

        return view();
    }
    public function delete(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){

            if($this->modeluser->where("id in (".input('id').")")->delete()){
                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeluser->getError(),"status"=>500]);
            }
        }
    }
    public function member_show(){
         $result=$this->modeluser->where("id",input('id'))->select()->toArray()[0];

         $this->assign([
             "result"=>$result,
             "head"=> md5(strtolower(trim($result["email"])))
         ]);

         return view();
    }

}