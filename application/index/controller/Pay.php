<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/20 0020
 * Time: 下午 10:42
 */

namespace app\index\controller;
use think\Cookie;
use app\index\controller\Common;
use think\Session;

class Pay extends Common
{
    public function init(){

    }
    public function index(){
        $result=$this->modeluser->where('id',Cookie::get("cid"))->where('status',1)->select()->toArray()[0];


        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
            "cart_num"=>$this->cart_num,
            "banner"=>$this->banner,
            "webuploader"=>$this->webuploader,
            "column"=>$this->column,
            "bgcolor"=>$this->bgcolor,
            'links'=>$this->links,
            "title"=>__TITLE__,
            "icp"=>__ICP__,
              "copyright"=>__COPYRIGHT__,
            "logo"=>__LOGO__,


        ]);

        return view();
    }

    public function add(){

        if(request()->isPost()){
            $data=input('post.');
            if($data["vcode"]==Session::get("vcode")){

                unset($data["vcode"]);

                if($this->validateuser->check($data)){
                    if($this->modeluser->where('id',Cookie::get('cid'))->update(["pay"=>md5($data["pay"])])!==false){
                    $this->success("支付密码修改成功",'index/index');
                    }else{
                    $this->error($this->modeluser->getError());
                    }
                }else{
                    $this->error($this->validateuser->getError());
                }
            }else{
                $this->error("验证码错误，请重新输入！");
            }
        }
    }



}