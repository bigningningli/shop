<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/4 0004
 * Time: 下午 11:58
 */

namespace app\index\controller;

use app\index\controller\Common;
use think\Cookie;
use think\Db;
use think\Session;

class Order extends Common
{
    protected $result;

    public function init(){
        if(!Cookie::has('cid')){
            $this->error("请先登录","login/index");
        }
        $this->result=$this->modelorder->where('user_id',Cookie::get('cid'))->select()->toArray();
        foreach ($this->result as $key=>$value){

            $row=Db::table("think_orderdetails")->alias("o")->field('o.id,o.gid,o.model,o.num,g.title,g.images,g.price')->join("think_goods g","o.gid=g.id")->where('order_id',$value["id"])->select();
            if(!count($row)){
                continue;
            }
            foreach ($row as $k=>$v)
                $row[$k]["images"]=substr($v["images"],0,strpos($v["images"],","));

            $this->result[$key]=array_merge($value,["items"=>$row]);
        }

    }
    public function index(){

        $this->assign([
            "result"=>$this->result,
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
    public function pay(){
        $address=$this->modeladdress->where('uid',Cookie::get('cid'))->select()->toArray();
        $goods=Db::name("orderdetails")->alias('o')->where('order_id',input('id'))->field('o.id,order_id,gid,o.model,num,title,images,inventory,buy_min_number,buy_max_number,price')->join('goods g','g.id = o.gid')->select();
        $order=$this->modelorder->where('id',input('id'))->select()->toArray()[0];

        if($order["user_id"]!=Cookie::get('cid')){
            $this->error("错误的操作",'index/index');
        }
        foreach ($goods as $key=>$value){
            $goods[$key]["images"]=substr($value["images"],0,strpos($value["images"],","));
        }
        $this->assign([
            "order"=>$order,
            "address_num"=>count($address),
            "address"=>$address,
            "goods"=>$goods,
            "cart_num"=>$this->cart_num,
            "goodscategoryarr"=>$this->arr,
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
    public function num_judge(){

        if(request()->isPost()){
            $data=input('post.');
            $result=$this->modelorderdetails->where('id',$data["id"])->field('order_id,gid')->select()->toArray()[0];

            $numresult=$this->modelorderdetails->field('sum(num) as num')->group('order_id,gid')->having('gid='.$result["gid"].'&&order_id='.$result["order_id"])->select()->toArray();



            $num=count($numresult)?$numresult[0]["num"]:0;
            $num+=(int)$data["num"];

            $row=$this->modelgoods->where('id',$result["gid"])->field('inventory,buy_min_number,buy_max_number')->select()->toArray()[0];


            if($num>$row["inventory"]){
                return json_encode(["msg"=>"超过库存","status"=>500]);
            }
            if($num>$row["buy_max_number"]){
                return json_encode(["msg"=>"超过最大购买数量","status"=>500]);
            }
            if($num<$row["buy_min_number"]){
                return json_encode(["msg"=>"小于起购数量","status"=>500]);
            }

            if($this->modelorderdetails->where('id',$data["id"])->update(["num"=>(int)$data["old"]+(int)$data["num"]])===false){
                return json_encode(["msg"=>$this->modelorderdetails->getError(),"status"=>500]);
            }else{
                return json_encode(["status"=>200]);
            }
        }
    }
    public function address_selected(){
        if(request()->isPost()){
            if($this->modelorder->where('id',input('id'))->update(['receive_id'=>input('receive_id')])){
                return json(["status"=>200]);
            }else{
                return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
            }
        }
    }
    public function payment(){
        if(request()->isGet()){
            $fileroot=$_SERVER["DOCUMENT_ROOT"].'/'.explode("/",$_SERVER["SCRIPT_NAME"])[1].'/vendor/codepay/';
            if(!file_exists($fileroot)){
                $this->error("配置文件不存在",'index/index');
            }
            $result=$this->modelorder->where('id',input('id'))->select()->toArray()[0];
            $url=$_SERVER["REQUEST_URI"];
            substr($url,0,strlen($url));

            Session::set("id",$result["id"]);
            require_once($fileroot."codepay_config.php");
            require_once ($fileroot."codepay.php");


        }
    }
    public function pair(){
        if(!Session::has("id")||$this->modelorder->where('order_sn',input('pay_no'))->count()){
            $this->error("请通过正确方式访问此网页",'index/index');
        }

        if($this->modelorder->where('id',Session::get('id'))->field('money')->select()->toArray()[0]["money"]!=Db::table('codepay_order')->where('pay_no',input('pay_no'))->field('money')->select()[0]["money"]){
            $this->error("金额错误",'order/index');
        }
        $row=$this->modelorderdetails->where('order_id',Session::get('id'))->field('gid,num')->select()->toArray();
        $this->modelgoods->startTrans();
        foreach ($row as $value){
           if(!$this->modelgoods->where('id',$value["gid"])->setInc('sales_count',$value["num"])){
               $this->modelgoods->rollback();
               $this->error($this->modelgoods->getError(),'order/index');
           }
        }
        if($this->modelorder->where('id',Session::get('id'))->update(["order_sn"=>input('pay_no'),"status"=>1])){
            Session::delete('id');
            $this->modelgoods->commit();
            $this->success("宝贝正在飞奔向你",'home/index');
        }else{
            Session::delete('id');
            $this->modelgoods->rollback();
            $this->error($this->modelorder->getError(),'order/index');
        }

    }
    public function info(){
        $order=$this->modelorder->where('id',input('id'))->select()->toArray()[0];
        $orderdetails=Db::table("think_orderdetails")->alias("o")->field('o.id,o.gid,o.model,o.num,g.title,g.images,g.price')->join("think_goods g","o.gid=g.id")->where('order_id',$order["id"])->select();
        $address=$this->modeladdress->where('id',$order["receive_id"])->select()->toArray()[0];
        foreach ($orderdetails as $key=>$value)
        $orderdetails[$key]["images"]=substr($value["images"],0,strpos($value["images"],","));
        $this->assign([
            "order"=>$order,
            "orderdetails"=>$orderdetails,
            "address"=>$address,
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
    public function logistics(){

    }
    public function status(){
        if(request()->isPost()){
            if($this->modelorder->where('id',input('id'))->update(["status"=>input('status')])){
                    return json(["msg"=>"操作成功","status"=>200]);
            }else{
                    return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
            }
        }
    }
    public function refund(){
        if(request()->isPost()){
            $this->modelorder->startTrans();
            $old=$this->modelorder->where('id',input('id'))->field('status')->select()->toArray()[0]["status"];

            if(!$this->modelorder->where('id',input('id'))->update(["status"=>4])){
                $this->error($this->modelorder->getError());
            }
            $data=input('post.');
            if($old!=6)
             $data=array_merge($data,["order_id"=>input('id'),"old"=>$old]);
            else{
             $data=array_merge($data,["order_id"=>input('id')]);
             if($this->modelrefund->where('order_id',input('id'))->delete()===false){
                $this->modelorder->rollback();
                 $this->error($this->modelrefund->getError());
             }
            }
            unset($data["id"]);
            if($this->modelrefund->save($data)){
                $this->modelorder->commit();
                $this->success("申请成功,等待商家回复",'index');
            }else{
                $this->modelorder->rollback();
                    $this->error($this->modelrefund->getError());
            }
        }


        $result=$this->order_select(input('id'));

        $this->assign([
            "result"=>$result,
            "cart_num"=>$this->cart_num,
            "goodscategoryarr"=>$this->arr,
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
    public function refund_cancel(){

        if(request()->isPost()){
            $this->modelorder->startTrans();
            if(!$this->modelorder->where('id',input('id'))->update(["status"=>$this->modelrefund->where('order_id',input('id'))->field('old')->select()->toArray()[0]["old"]])){
                return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
            }
            if($this->modelrefund->where('order_id',input('id'))->delete()){
                $this->modelorder->commit();
                return json(["msg"=>"取消成功","status"=>200]);
            }else{
                $this->modelorder->rollback();
                return json(["msg"=>$this->modelrefund->getError(),"status"=>500]);
            }
        }
    }
    public function delete(){
        if(request()->isPost()){
                $this->modelorder->startTrans();
                if($this->modelorder->where('id',input('id'))->delete()===false){
                    return json(["msg"=>$this->modelorder->getError(),"status"=>500]);
                }
                if($this->modelorderdetails->where('order_id',input('id'))->delete()==false){
                    $this->modelorder->rollback();
                    return json(["msg"=>$this->modelorderdetails->getError(),"status"=>500]);
                }else{
                    $this->modelorder->commit();
                    return json(["msg"=>"删除成功","status"=>200]);
                }
        }
    }
    public function order_select($id){
        $result=$this->modelorder->where('id',$id)->select()->toArray()[0];


            $row=Db::table("think_orderdetails")->alias("o")->field('o.id,o.gid,o.model,o.num,g.title,g.images,g.price')->join("think_goods g","o.gid=g.id")->where('order_id',$result["id"])->select();
            if(!count($row)){
                return $result;
            }
            foreach ($row as $k=>$v)
                $row[$k]["images"]=substr($v["images"],0,strpos($v["images"],","));

            $result=array_merge($result,["items"=>$row]);
        return $result;
    }

    public function change(){

        $this->assign([

            "result"=>$this->result,
            "cart_num"=>$this->cart_num,
            "goodscategoryarr"=>$this->arr,
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
}