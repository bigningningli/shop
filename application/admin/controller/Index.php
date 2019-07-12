<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16 0016
 * Time: 11:28
 */

namespace app\admin\controller;


use think\Cookie;
use think\Request;
use think\Controller;
use think\Loader;
use think\Session;
use think\captcha;
use app\admin\model\AuthMember;
class Index extends Controller
{
    public $auth;
    public $modelmember;
    public function _initialize()
    {
        $this->modelmember=new AuthMember();
    }

    public function index(){
        if(!Cookie::has("username")){
            $this->error("请登录后访问此网址！",'login');
        }
        $mlist=$this->modelmember->where('id',Cookie::get("uid"))->select()[0];
        $this->assign([
            "mlist"=>$mlist,
        ]);
        return view();
    }
    public function welcome(){
        return view();
    }
    public function login(){
        if(request()->isPost()){
            $captcha=new captcha\Captcha();
            if($captcha->check(input('verify'))){

                $data=$this->modelmember->where('username',input('username'))->where('userpassword',md5(input('userpassword')))->column('username,id,userpassword,status');

                if(count($data)==0){
                    $this->error("用户名或密码不正确！请重新输入！");
                }else{
                    $data=$data[input('username')];
                    if(!$data["status"]){
                        $this->error("该用户已被管理员停用！");
                    }
                    if(input('online')=="1"){

                        Cookie::set("uid",$data["id"],60*60*24*7);
                        Cookie::set("username",input("username"),60*60*24*7);

                    }else{
                        Cookie::set("uid",$data["id"]);
                        Cookie::set("username",input("username"));
                    }
                    $this->success("登陆成功！",'admin\index');
                }

            }else{

                $this->error("验证码输入错误，请重新输入！");

            }

        }
            return view();

    }
    public function log_off(){
        Cookie::delete("uid");
        Cookie::delete("username");
                $this->success("注销成功！","\index");
    }
    public function verify(){
        ob_clean();
        $captcha=new captcha\Captcha();
        $captcha->fontSize = 16;
        $captcha->length   = 4;
        $captcha->reset=true;
        return $captcha->entry();
    }
}