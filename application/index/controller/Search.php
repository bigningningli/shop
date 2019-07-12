<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/22 0022
 * Time: 下午 9:56
 */

namespace app\index\controller;

use app\index\controller\Common;
class Search extends Common
{
    public function init(){

    }
    public function index(){
        $categoryarr=array();
        if(input('type')=="title"){
            $result=$this->modelgoods->where('title|content','like','%'.input('content').'%')
                ->order(input('sort'))
                ->paginate(8,false,[

                'query'=>request()->param()

                ]);
        }else{

            $str=substr($this->dsort(input('content')),0,strlen($this->dsort(input('content')))-1);


            $result=$this->modelgoods->where('category in ('.$str.')')->paginate(8,false,[

                'query'=>request()->param()

            ]);

            for($i=1;$i<=2;$i++){
                $arr=$this->modelgoodscategory->where('level',$i)->select()->toArray();
                if(count($arr)){
                    array_push($categoryarr,$arr);
                }else{
                    break;
                }
            }

        }
        foreach ($result as $key=>$value){
            $result[$key]["images"]=explode(",",$value["images"])[0];
        }
       $this->assign([
           "cart_num"=>$this->cart_num,
           "result"=>$result,
           "banner"=>$this->banner,
           "webuploader"=>$this->webuploader,
           "column"=>$this->column,
           "bgcolor"=>$this->bgcolor,
           'links'=>$this->links,
           "title"=>__TITLE__,
           "icp"=>__ICP__,
            "copyright"=>__COPYRIGHT__,
            "logo"=>__LOGO__,
           "categoryarr"=>$categoryarr,
           "selfurl"=>substr(request()->url(),0,strpos(request()->url(),'&page')==0?strlen(request()->url()):strpos(request()->url(),'&page'))
       ]);

     return view();
    }
   public function dsort($id){
       $str=$id.",";
       $result=$this->modelgoodscategory->where('pid',$id)->field('id,pid')->select()->toArray();
        if(count($result)){
            foreach ($result as $value){
                $str.=$this->dsort($value["id"]);
            }
        }
        return $str;
   }
}