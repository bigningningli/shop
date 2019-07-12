<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16 0016
 * Time: 上午 12:06
 */

namespace app\admin\controller;
use app\admin\controller\Common;
use think\Db;
use think\Cookie;
class Order extends Common
{
    public function init(){

    }
    public function index(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        return view();
    }
    public function unshipped(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->row(1);
        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
        ]);
        return view();
    }
    public function unconfirmed(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->row(2);
        $result=array_merge($result,$this->row(6));
        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
        ]);
        return view();
    }
    public function refund(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->row(4);
        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
        ]);
        return view();
    }

    public function refunded(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->row(5);
        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
        ]);

        return view();
    }
    public function confirmed(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->row(7);
        $this->assign([
            "result"=>$result,
            "sum"=>count($result),
        ]);
        return view();
    }
    public function row($s){
        $result=$this->modelorder->where('status',$s)->select()->toArray();
        foreach ($result as $key=>$value){

            $row=Db::table("think_orderdetails")->alias("o")->field('o.id,o.gid,o.model,o.num,g.title,g.price')->join("think_goods g","o.gid=g.id")->where('order_id',$value["id"])->select();
            if(!count($row)){
                continue;
            }

            $result[$key]=array_merge($value,["items"=>$row]);
            $result[$key]=array_merge($result[$key],["customer"=>$this->modeluser->where('id',$result[$key]["user_id"])->select()->toArray()[0]["name"]]);
            if($s==4||$s==5) {
                $t=$this->modelrefund->where('order_id',$value["id"])->field('reason')->select()->toArray();
                if(!count($t)){
                    continue;
                }
                $result[$key] = array_merge($result[$key], ["refund"=>$t[0]["reason"]]);
            }
        }


        return $result;
    }
    public function delete(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get('uid'))){
          return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){

            $data=explode(',',input('id'));
            $this->modelorder->startTrans();
            if(!$this->modelorder->where('id in ('.input('id').')')->delete()){
                return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
            }
            foreach ($data as $val){
                if(!$this->modelorderdetails->where('order_id',$val)->delete()){
                    $this->modelorder->rollback();
                    return json(["msg"=>$this->modelorderdetails->getError(),"status"=>500]);
                }
            }
            $this->modelorder->commit();
            return json(["msg"=>"关闭成功","status"=>200]);

        }
    }
    public function add_logistics(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get('uid'))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            if($this->modelorder->where('id',input('id'))->update(["status"=>2,"ec"=>input('ec'),"logistics"=>input('logistics')])){
                return json(["msg"=>"添加成功","status"=>200]);
            }else{
                return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
            }
        }
        $express_company=$this->modelec->select()->toArray();
        $this->assign([
            "express_company"=>$express_company
        ]);
        return view();
    }
    public function refund_description(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modelrefund->where("order_id",input('id'))->field('type,reason,description')->select()->toArray()[0];
        $this->assign([
            "result"=>$result,
        ]);
        return view();
    }
   public function refund_status(){
       if(!$this->auth->check(request()->controller().'/edit',Cookie::get('uid'))){
           return json(["msg"=>"权限不足","status"=>403]);
       }
        if(request()->isPost()) {

            if ($this->modelorder->where('id', input('id'))->update(["status" => input('status')])) {

                return json(["msg" => "操作成功", "status" => 200]);
            } else {

                return json(["msg" => $this->modelorder->getError(), "status" => 500]);
            }
        }
   }





}