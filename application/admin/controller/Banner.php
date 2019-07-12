<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19 0019
 * Time: 下午 5:35
 */

namespace app\admin\controller;
use app\admin\controller\Common;
use think\Cookie;
class Banner extends Common
{
    public function init(){

    }
    public function index(){
        if(!$this->auth->check('layout/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modelbanner->order('sort')->select();
        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
            "webuploader"=>$this->webuploader
        ]);
        return view();
    }
    public function add(){
        if(!$this->auth->check('layout/add',Cookie::get('uid'))){
            return view('./error/authority');
        }
        if(request()->isPost()) {
            $data=input('post.');
            if($this->modelbanner->allowField(true)->validate(true)->save(
                $data
            )){
                return json_encode(["msg"=>"添加成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeadvertisement->getError(),"status"=>500]);
            }
        }
        return view();
    }
    public function edit(){
        if(!$this->auth->check('layout/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }

        $result=$this->modelbanner->where("id",input('id'))->select()->toArray()[0];

        if(!$result){
            return view('/error/404');
        }

        if(request()->isPost()) {
            $id=["id"=>input('id')];
            $data=input('post.');
            unset($data["id"]);
            $this->modelbanner->startTrans();
            if($this->modelbanner->allowField(true)->validate(true)->save(
                $data,$id
            )!==false){
                if ($this->imgdel($result["imagesrc"],$data["imagesrc"])) {
                    $this->modelbanner->commit();
                    return json_encode(["msg"=>"修改成功","status"=>200]);
                }else{
                    $this->modelbanner->rollback();

                    return json_encode(["msg"=>"删除图片失败","status"=>500]);
                }
            }else{
                    $this->modelbanner->rollback();
                return json_encode(["msg"=>$this->modelbanner->getError(),"status"=>500]);
            }

        }

        $this->assign([
            "result"=>$result,
            "webuploader"=>$this->webuploader
        ]);
        return view();
    }
    public function status(){
        if(!$this->auth->check('layout/edit',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            if($this->modelbanner->where('id',input('id'))->update(['status'=>input('data')])===false){
                return json_encode(["msg"=>$this->modelbanner->getError(),"status"=>500]);
            }else{
                return json_encode(["status"=>200]);
            }

        }
    }

    public function delete(){


        if(!$this->auth->check('layout/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()) {
            $this->modelbanner->startTrans();
            $arr = $this->modelbanner->where("id in (" . input('id') . ")")->column('imagesrc');
            foreach ($arr as $value) {
                if (!unlink($_SERVER["CONTEXT_DOCUMENT_ROOT"].$this->webuploader . $value)) {
                    $this->modelbanner->rollback();
                    return json_encode(["msg" => "删除失败", "status" => 500]);
                }
            }

        if($this->modelbanner->where("id in (".input('id').")")->delete()){
            $this->modelbanner->commit();
            return json_encode(["msg"=>"删除成功","status"=>200]);
        }else{
            $this->modelbanner->rollback();
            return json_encode(["msg"=>$this->modelbanner->getError(),"status"=>500]);
        }


        }

    }
}