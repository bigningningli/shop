<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/16 0016
 * Time: 上午 3:56
 */

namespace app\index\controller;

use app\index\controller\Common;
use think\Cookie;
use think\Session;

class Home extends Common
{
    public function init(){
            if(!Cookie::has("customer")){
                $this->redirect('login/index');
            }
    }
    public function index(){
        $unpaid=$this->modelorder->where('user_id',Cookie::get('cid'))->where('status',0)->count();
        $unshipped=$this->modelorder->where('user_id',Cookie::get('cid'))->where('status',1)->count();
        $unreceived=$this->modelorder->where('user_id',Cookie::get('cid'))->where('status',2)->count();
        $uncomment=$this->modelorder->where('user_id',Cookie::get('cid'))->where('status',3)->count();
        $replacement=$this->modelorder->where('user_id',Cookie::get('cid'))->where('status',4)->count();
        $this->assign([
            "unpaid"=>$unpaid,
            "unshipped"=>$unshipped,
            "unreceived"=>$unreceived,
            "uncomment"=>$uncomment,
            "replacement"=>$replacement,
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
    public function information(){
        $province=$this->modelregion->where('level',1)->select()->toArray();
        $result=$this->modeluser->where('id',Cookie::get("cid"))->field('id,name,sex,email,birthday,phone,location')->where('status',1)->select()->toArray()[0];
        $this->assign([
            "result"=>$result,
            "banner"=>$this->banner,
            "webuploader"=>$this->webuploader,
            "cart_num"=>$this->cart_num,
            "column"=>$this->column,
            "bgcolor"=>$this->bgcolor,
            'links'=>$this->links,
            "title"=>__TITLE__,
            "icp"=>__ICP__,
              "copyright"=>__COPYRIGHT__,
            "logo"=>__LOGO__,
            "province"=>$province
        ]);

       return view();
    }
    public function edit(){
        if(request()->isPost()){

            $data=input('post.');
            if(count($this->modeluser->where("phone",$data["phone"])->where('id','neq',$data["id"])->select()->toArray())){
                $this->error("该手机号已被注册！");
            }
            if(count($this->modeluser->where("email",$data["email"])->where('id','neq',$data["id"])->select()->toArray())){
                $this->error("该邮箱已被注册！");
            }

            if($this->modeluser->allowField(true)->save($data,["id"=>Cookie::get("id")])!==false){
               $this->success("修改成功",'index');
            }else{
                $this->error($this->modeluser->getError());
            }
        }
    }


}