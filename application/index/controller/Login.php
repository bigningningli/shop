<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11 0011
 * Time: 下午 1:10
 */

namespace app\index\controller;
use app\index\controller\Common;
use think\Cookie;
use think\Session;

class Login extends Common
{
        public function init(){

        }
        public function index(){
            $links=$this->modellinks->where('status',1)->field('name,url,description')->select()->toArray();
            $this->assign([
                'links'=>$links,
                "webuploader"=>$this->webuploader,
                "column"=>$this->column,
                "title"=>__TITLE__,
                "icp"=>__ICP__,
                "copyright"=>__COPYRIGHT__,
                "logo"=>__LOGO__,
            ]);

            return view();
        }
        public function login(){
                $getuser=$this->modeluser->where('status',1)->where('name|phone|email',input('name'))->select()->toArray();

                if(count($getuser)){
                    if($getuser[0]["password"]==md5(input('password'))){
                        Cookie::set("customer",$getuser[0]["name"]);
                        Cookie::set("email",$getuser[0]["email"]);
                        Cookie::set("cid",$getuser[0]["id"]);
                        $this->success("登录成功",'index/index');
                    }else{
                        $this->error("密码错误");
                    }
                }else{
                $this->error("此用户不存在");
            }
        }
}