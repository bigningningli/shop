<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/4 0004
 * Time: 下午 11:46
 */

namespace app\admin\controller;
use think\Controller;
use think\Cookie;
use think\Loader;
use think\Session;
use app\admin\controller\Common;
use app\admin\model\Advertisement as modeladvertisment;
use app\admin\validate\Column as validatecolumn;

class Advertisement extends Common
{
    public $modeadvertisement;
    public function init(){
            $this->modeadvertisement=new modeladvertisment();
    }
    public function index(){
        $result=$this->modeadvertisement->select()->toArray();
        $this->assign([
            "alist"=>$result,
            "sum"=>count($result),

        ]);
        return view();
    }
    public function add(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()) {
            $data=input('post.');
            if($this->modeadvertisement->allowField(true)->validate(true)->save(
               $data
            )){
                return json_encode(["msg"=>"添加成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeadvertisement->getError(),"status"=>500]);
            }
        }
        return view();
    }
    public function advertisement_list(){
        $result=$this->modeadvertisement->order('sort')->select();
        $this->assign([
            "alist"=>$result,

        ]);
            return view();
    }
    public function delete(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            if($this->modeadvertisement->where("id in (".input('id').")")->delete()){
              return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeadvertisement->getError(),"status"=>500]);
            }
        }
    }
    public function imgdel(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){

            if(unlink($_SERVER["CONTEXT_DOCUMENT_ROOT"].input('dir'))){
                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>"删除失败","status"=>500]);
            }

        }
    }
    public function edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()){
            $data=input('post.');
            $id=["id"=>input('id')];
            unset($data["id"]);
            if($this->modeadvertisement->allowField(true)->validate(true)->save(
                $data,$id
            )){
                    return json_encode(["msg"=>"修改成功","status"=>200]);
            }else{
                    return json_encode(["msg"=>$this->modeadvertisement->getError(),"status"=>500]);
            }
        }else{
            $result=$this->modeadvertisement->where("id",input('id'))->select()->toArray()[0];
        }
        $src=explode("?",$result["imagesrc"]);
        array_pop($src);
        $this->assign([
            "alist"=>$result,
            "src"=>$src,
            "uploadfile"=>__UPLOAD__
        ]);
        return view();
    }
    public function ee(){
       dump(input('post.'));
    }
}