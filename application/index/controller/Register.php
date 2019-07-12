<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11 0011
 * Time: 下午 1:09
 */

namespace app\index\controller;
use app\index\controller\Common;
use think\Session;

class Register extends Common
{
    public $vcode;
    public function init(){

    }
    public function index(){
        $links=$this->modellinks->where('status',1)->field('name,url,description')->select()->toArray();
        $this->assign([
            'links'=>$links,
            "title"=>__TITLE__,
            "icp"=>__ICP__,
            "copyright"=>__COPYRIGHT__,
            "logo"=>__LOGO__,
            "webuploader"=>$this->webuploader,
        ]);

        return view();
    }
    public function register(){
        $data=input('post.');
        $type="";
            if(isset($data["email"])){
                $type="email";
            }else{

                if($data["vcode"]!=Session::get("vcode")){
                    $this->error("验证码错误，请重新输入！");
                }
                $type="phone";
            }
        if(count($this->modeluser->where('name|'.$type,$data[$type])->select()->toArray())){
            $this->error("该".$type."已被注册");
        }else{
            $data=array_merge($data,["name"=>$data[$type]]);
            if(!$this->validateuser->check($data)){
                $this->error($this->validateuser->getError());
            }
            $data["password"]=md5($data["password"]);

            if($this->modeluser->allowField(true)->save($data)){
                Session::delete("vcode");
                $this->success("注册成功",'login/index');
            }else{
                $this->error($this->modeluser->getError());
            }
        }
    }



}