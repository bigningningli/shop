<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/16 0016
 * Time: 下午 7:47
 */

namespace app\index\controller;
use app\index\controller\Common;
use think\Cookie;

class Address extends Common
{
    public function init(){

    }
    public function index(){

        $result=$this->modeladdress->where('uid',Cookie::get("cid"))->select()->toArray();
        $province=$this->modelregion->where('level',1)->select()->toArray();
        $city=$this->modelregion->where('level',2)->select()->toArray();

        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
            "banner"=>$this->banner,
            "webuploader"=>$this->webuploader,
            "column"=>$this->column,
            "bgcolor"=>$this->bgcolor,
            'links'=>$this->links,
            "title"=>__TITLE__,
            "icp"=>__ICP__,
            "copyright"=>__COPYRIGHT__,
            "logo"=>__LOGO__,
            "province"=>$province,
            "city"=>$city,
            "cart_num"=>$this->cart_num,
        ]);
   return view();

    }
    public function add(){
        if(request()->isPost()) {
            $data = input('post.');
            $data["address"] = $this->modelregion->where('id', $data["pid"])->field('name')->select()->toArray()[0]["name"] . $data["address"];
            if ($this->modeladdress->allowField(true)->save($data)) {
                return json_encode(["status" => 200]);
            } else {
                return json_encode(["msg" => $this->modeladdress->getError(), "status" => 500]);
            }
        }
    }
    public function edit(){
        if(request()->isPost()){
            $data=input('post.');
            $id=["id"=>$data["id"]];
            unset($data["id"]);
            if($this->modeladdress->where('id',input('id'))->field('uid')->select()->toArray()[0]["uid"]!=Cookie::get('cid')){
                $this->error("非法操作");
            }
            if($this->modeladdress->allowField(true)->save($data,$id)){
                $this->success("修改成功",'index');
            }else{
                $this->error("修改失败");
            }
        }
        $result=$this->modeladdress->where('id',input('id'))->select()->toArray()[0];
        $province=$this->modelregion->where('level',1)->select()->toArray();
        $city=$this->modelregion->where('level',2)->select()->toArray();

        $this->assign([

            "cart_num"=>$this->cart_num,
            "result"=>$result,
            "banner"=>$this->banner,
            "webuploader"=>$this->webuploader,
            "column"=>$this->column,
            "bgcolor"=>$this->bgcolor,
            'links'=>$this->links,
            "title"=>__TITLE__,
            "icp"=>__ICP__,
            "copyright"=>__COPYRIGHT__,
            "logo"=>__LOGO__,
            "province"=>$province,
            "city"=>$city,

        ]);
        return view();

    }

    public function updefault(){
        $this->modeladdress->startTrans();
        if($this->modeladdress->where('id',input('aid'))->update(['default'=>1])){

           if($this->modeladdress->where('uid',Cookie::get("cid"))->where('id','neq',input('aid'))->update(["default"=>0])!==false){
                $this->modeladdress->commit();
                return json_encode(["status"=>200]);
            }else{
                $this->modeladdress->rollback();
                return json_encode(["msg"=>$this->modeladdress->getError(),"status"=>500]);
            }


        }else{
            $this->modeladdress->rollback();
            return json_encode(["msg"=>$this->modeladdress->getError(),"status"=>500]);
        }

    }
    public function delete(){
        if(request()->isPost()) {
            if($this->modeladdress->where('id',input('id'))->field('uid')->select()->toArray()[0]["uid"]!=Cookie::get(';cid')){

                return json_encode(["msg" =>"错误的操作", "status" => 500]);
            }
            if ($this->modeladdress->where('id', input('id'))->delete()) {
                return json_encode(["status" => 200]);
            } else {
                return json_encode(["msg" => $this->modeladdress->getError(), "status" => 500]);
            }
        }
    }

}