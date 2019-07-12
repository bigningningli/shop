<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27 0027
 * Time: 下午 1:41
 */

namespace app\admin\controller;
use think\Controller;
use think\Cookie;
use think\Loader;
use think\Session;
use app\admin\controller\Common;
use app\admin\model\Column as modelcolumn;
use app\admin\validate\Column as validatecolumn;
class Column extends Common
{
   public function init(){

    }
   public function index(){
       if(!$this->auth->check('layout/see',Cookie::get('uid'))){
           return view('./error/authority');
       }
        $result=$this->modelcolumn->select()->toArray();


       $this->assign([
           "columnlist"=>$result,
           "sum"=>count($result)
       ]);
       return view();
   }

   public function add(){
        if(!$this->auth->check('layout/add',Cookie::get("uid"))){
           return view('./error/authority');
       }
       $sort=$this->modelcolumn->order('sort')->select()->toArray();

       if(request()->isPost()){
           $data=input('post.');
           if($this->modelcolumn->where('sort','>',$data["sort"])->setInc('sort',1)===false) {
              return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
           }
           $data["sort"]+=1;
            if($this->modelcolumn->allowField(true)->validate(true)->save(
                    $data
            )){
              return json_encode(["msg"=>"添加成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
            };

       }
       $this->assign([
           'sort'=>$sort,
       ]);
        return view();
   }
   public function edit(){
       if(!$this->auth->check('layout/edit',Cookie::get("uid"))){
           return view('./error/authority');
       }
       $sort=$this->modelcolumn->order('sort')->where('id','<>',input('id'))->select()->toArray();
       $sortselect=$this->modelcolumn->order('sort','desc')->where('sort','<',input('sort'))->field('id')->select()->toArray();
        if(count($sortselect)){
        $sortselect=$sortselect[0]["id"];
        }else{
            $sortselect=-1;
        }
       if(request()->isPost()){
           $stemp=$this->modelcolumn->where('id',input('id'))->select()->toArray()[0]["sort"];
            $data=input('post.');
           $id=array("id"=>input('id'));
            unset($data["id"]);
            $this->modelcolumn->startTrans();
                if($stemp<input('sort')){
                    if($this->modelcolumn->where('sort',"<=",input('sort'))->where('sort',">",$stemp)->setDec('sort',1)===false)
                    {
                        $this->modelcolumn->rollback();
                        return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
                    }
                }
                if($stemp>=input('sort')) {
                    if ($this->modelcolumn->where('sort', ">", input('sort'))->where('sort', "<=", $stemp)->setInc('sort', 1) === false)
                    {
                        $this->modelcolumn->rollback();
                        return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
                    }
                    $data["sort"]+=1;
                }

           if($this->modelcolumn->allowField(true)->validate(true)->save(
               $data,$id
           )===false){
               $this->modelcolumn->rollback();
               return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
           }else{
               $this->modelcolumn->commit();
               return json_encode(["msg"=>"修改成功","status"=>200]);
           };

       }else{
           $result=$this->modelcolumn->where('id',input('id'))->select()->toArray()[0];
       }
       $this->assign([
           "sort"=>$sort,
           "result"=>$result,
           "ss"=>$sortselect
       ]);
       return view();
   }
   public function status(){
       if(!$this->auth->check('layout/edit',Cookie::get("uid"))){
           return json_encode(["msg"=>"权限不足","status"=>403]);
       }
       if(request()->isPost()){
           if($this->modelcolumn->where('id',input('id'))->update(['status'=>input('data')])===false){
              return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
           }else{
               return json_encode(["status"=>200]);
           }

       }
   }
   public function delete(){
       if(!$this->auth->check('layout/delete',Cookie::get("uid"))){
           return json_encode(["msg"=>"权限不足","status"=>403]);
       }
        if(request()->isPost()){
            $d=explode(',',input('id'));
            foreach ($d as $v){
                $this->modelcolumn-> where('sort','>',$this->modelcolumn->where('id',$v)->column('sort')[0])->setDec('sort',1);

            }
            if($this->modelcolumn->where("id in (".input('id').")")->delete()){
                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelcolumn->getError(),"status"=>500]);
            }
        }
   }



}