<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/22 0022
 * Time: 下午 9:52
 */

namespace app\index\controller;


use think\Cookie;

class Introduction extends Common
{
    protected $buy_min_number;
    protected $buy_max_number;
    protected $inventory;

    public function init(){
        $result=$this->modelgoods->where('id',input('id'))->field('inventory,buy_min_number,buy_max_number')->select()->toArray()[0];
        $this->inventory=$result["inventory"];
        $this->buy_min_number=$result["buy_min_number"];
        $this->buy_max_number=$result["buy_max_number"];
    }
    public function index(){


        $result=$this->modelgoods->where('id',input('id'))->select()->toArray()[0];

        $images=explode(",",$result["images"]);

        $model=explode(",",$result["model"]);

        $goodscategory=$this->dcategory($result["category"]);

        $goodscategorypop=end($goodscategory)["name"];

        $similar=$this->modelgoods->where('category',$result["category"])->where('id','<>',input('id'))->field('id,title,images,price')->select()->toArray();

        foreach ($similar as $key=>$value)
        $similar[$key]["images"]=explode(",",$value["images"])[0];

        array_pop($images);

        if(end($model)=="")
        array_pop($model);

        unset($result["images"]);


        $this->assign([
            "buy_min_number"=>$this->buy_min_number,
            "buy_max_number"=>$this->buy_max_number,
            "inventory"=>$this->inventory,
            "cart_num"=>$this->cart_num,
            "similar"=>$similar,
            "result"=>$result,
            "images"=>$images,
            "goodscategory"=>$goodscategory,
            "goodscategorypop"=>$goodscategorypop,
            "place_origin"=>$this->modelregion->where('id',$result["place_origin"])->field('name')->select()->toArray()[0],
            "model"=>$model,
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
    public function dcategory($id){
        $arr=array();
        while ($id) {
            $result = $this->modelgoodscategory->where("id", $id)->select()->toArray()[0];
            array_unshift($arr,["id"=>$result["id"],"name"=>$result["name"]]);

            $id=$result["pid"];
        }
        return $arr;
    }
    public function addcart(){
            if(request()->isPost()){

                    if(!Cookie::has("cid")){
                        return json_encode(["msg"=>"请先登录","status"=>500]);
                    }

                    if($this->modelcart->where("gid",input('id'))->where("cid",Cookie::get('cid'))->where('model',input('model'))->count()){



                        $old_num=$this->modelcart->old_number(input('id'),Cookie::get('cid'));


                        if((int)input('num')+$old_num>$this->inventory||(int)input('num')+$old_num>$this->buy_max_number){
                            return json_encode(["msg"=>"超过最大购买数量或库存不足","status"=>500]);
                        }

                        if($this->modelcart->where("gid",input('id'))->where("cid",Cookie::get('cid'))->where('model',input('model'))->setInc('num',input('num')))
                        return json_encode(["msg"=>"添加成功","status"=>200]);
                        else{
                            return json_encode(["msg"=>"加入购物车失败","status"=>500]);
                        }
                    }else{
                        if($this->modelcart->allowField(true)->save(
                            ["gid"=>input('id'),"cid"=>Cookie::get('cid'),"model"=>input('model'),"num"=>input('num')]
                        ))
                            return json_encode(["msg"=>"添加成功","status"=>200]);
                        else{
                            return json_encode(["msg"=>"加入购物车失败","status"=>500]);
                        }
                    }
            }
    }



}