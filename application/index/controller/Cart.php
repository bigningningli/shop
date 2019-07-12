<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/29 0029
 * Time: 下午 8:00
 */

namespace app\index\controller;

use app\admin\model\Goods;
use app\index\controller\Common;
use think\Cookie;
use think\Db;

class Cart extends Common
{
    protected $c_address;
    public function init(){

        if(!Cookie::has("customer")){
            if(request()->action()=="purchase"){
                return ;
            }
            $this->error("请先登录", 'login/index');
        }

        $this->c_address=$this->modeladdress->where('uid',Cookie::get("cid"))->where('default',1)->select()->toArray()[0]["id"];
    }
    public function index(){
        $result=array();

        $sum=0.00;

        $result=Db::table('think_cart')->field('think_cart.id,think_cart.model,gid,cid,think_goods.images,title,num,original_price,price,think_cart.update_time')->where('cid',Cookie::get('cid'))->join('think_goods','think_goods.id = think_cart.gid')->order('gid,think_cart.update_time')->select();


        foreach ($result as $key=>$value){
           $result[$key]["images"]=explode(",",$value["images"])[0];
           $sum+=$value["price"]*$value["num"];
        }
        $this->assign([
            "result"=>$result,
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
            "sum"=>$sum,
        ]);

        return view();
    }
    public function cookie_cart(){





    }
    public function model_show(){
        $row=$this->modelcart->where('id',input('id'))->select()->toArray()[0];
        $model=$this->modelgoods->where('id',$row["gid"])->field('model')->select()->toArray()[0]["model"];
        $arr=explode(",",$model);
        if(end($arr)=="")
        array_pop($arr);
        $this->assign([
            "selected"=>$row["model"],
            "model"=>$arr
        ]);
        return view();
    }
    public function model_edit(){
        $row=$this->modelcart->where('id',input('id'))->select()->toArray()[0];
        if($this->modelcart->where("gid",$row['gid'])->where("cid",$row['cid'])->where('model',input('model'))->count()){
            if($this->modelcart->where("gid",$row['gid'])->where("cid",$row['cid'])->where('model',input('model'))->setInc('num',$row["num"])&&$this->modelcart->where('id',input('id'))->delete()){
                return json_encode(["status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelcart->getError(),"status"=>500]);
            }
        }else{
            if($this->modelcart->where('id',input('id'))->update(["model"=>input('model')])===false){
                return json_encode(["msg"=>$this->modelcart->getError(),"status"=>500]);
            }else{
                return json_encode(["status"=>200]);
            }
        }
    }
    function del(){
        if($this->modelcart->where('id in ('.input('id').')')->delete()){
            return json_encode(["status"=>200]);
        }else{
            return json_encode(["msg"=>$this->modelcart->getError(),"status"=>500]);
        }

    }
    function settlement(){
        $result=$this->modelcart->where('id in ('.input('id').')')->select()->toArray();
        foreach ($result as $value){
            $row=$this->modelgoods->where('id',$value["gid"])->filed('inventory,buy_min_number,buy_max_number')->select()->toArray()[0];
            if($value["num"]>$row["inventory"]){
                return json(["msg"=>"数量超出库存","status"=>500]);
            }
            if($value["num"]>$row["buy_max_number"]){
                return json(["msg"=>"数量超出限购","status"=>500]);
            }
            if($value["num"]<$row["buy_min_number"]){
                return json(["msg"=>"数量低于起购","status"=>500]);
            }
        }

        if($this->modelorder->save(["user_id"=>Cookie::get('cid'),"receive_id"=>$this->c_address])===false){
            return json_encode(["msg"=>$this->modelorder->getError(),"status"=>500]);
        }
        $oid=$this->modelorder->getLastInsID();
        $this->modelorderdetails->startTrans();
        foreach ($result as $value){
            if(!$this->modelorderdetails->save(["order_id"=>$oid,"gid"=>$value["gid"],"model"=>$value["model"],"num"=>$value["num"]])){
                $this->modelorderdetails->rollback();
                return json_encode(["msg"=>$this->modelorderdetails->getError(),"status"=>500]);
            }
        }
        $this->modelorderdetails->commit();
        return json(["msg"=>$oid,"status"=>200]);
    }
    public function purchase(){

        if(!Cookie::has("customer")){
            return json(["msg"=>"请先登录","status"=>500]);
        }
        $price=$this->modelgoods->where('id',input('gid'))->field('price')->select()[0]["price"];

        if(!$this->modelorder->save(["user_id"=>Cookie::get('cid'),"receive_id"=>$this->c_address,"money"=>$price*intval(input('num'))])){
            return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
        }

        $oid=$this->modelorder->getLastInsID();

        if($this->modelorderdetails->save(["order_id"=>$oid,"gid"=>input('gid'),"model"=>input('model'),"num"=>input('num')])){
            return json(["msg"=>$oid,"status"=>200]);
        }else{
            return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
        }
    }


}