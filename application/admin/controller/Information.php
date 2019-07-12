<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/4 0004
 * Time: 下午 3:34
 */

namespace app\admin\controller;

use think\Controller;
use think\Cookie;
use think\Loader;
use think\response\Json;
use think\Session;
use think\Db;
use app\admin\controller\Common;
use app\admin\model\Column as modelcolumn;
use app\admin\model\Information as modelinformation;
use app\admin\model\Type as modeltype;
use app\admin\validate\Column as validatecolumn;
class Information extends Common
{
    public $modelcolumn;
    public $modelinformation;
    public $modeltype;
    public $class;
    public function init(){
        $this->modelcolumn=new modelcolumn();
        $this->modelinformation=new modelinformation();
        $this->modeltype=new modeltype();
        $this->class=array();
    }
    public function index(){

        $stemp=null;

        if(Session::get('stemp')==0||Session::get('table')!='information'){
            Session::clear();
        }
        $sql="select think_information.id,think_information.title,think_information.sources,think_information.update_time,think_information.status,think_information.browse,think_type.name from think_information left join think_type on think_information.type=think_type.id";
        if((Session::get("table")=="information")||(request()->isPost()&&(input('datemin')!=""||input('datemax')!=""||input('selectuser')!="")||input('class')!=0)){
            if(request()->isPost()) {
                Session::set("datemin", strtotime(input('datemin')));
                Session::set("datemax", strtotime(input('datemax').' 24:00:00'));
                Session::set("select", input("selectuser"));
                Session::set("class",input('class'));
                Session::set("table","information");
            }

           if(Session::get("datemin")!=""){
                $sql.=" where think_information.update_time>=".Session::get("datemin");
           }
           if(Session::get("datemax")!=""){
               if(Session::get("datemin")!=""){
                   $sql.=" and ";
               }else{
                   $sql.=" where ";
               }
               $sql.="think_information.update_time<=".Session::get("datemax");
           }
            if(Session::get("select")!=""){
                if(Session::get("datemin")!=""||Session::get("datemax")){
                    $sql.=" and ";
                }else{
                    $sql.=" where ";
                }
                $sql.="think_information.title=".Session::get("select");

            }
            if(Session::get("class")!=0){
                if(Session::get("datemin")!=""||Session::get("datemax")||Session::get("select")!=""){
                    $sql.=" and ";
                }else{
                    $sql.=" where ";
                }
                $sql.="think_information.type=".Session::get("class");

            }
            $stemp=1;
        }



        $toplist=$this->modeltype->where('lever',1)->select()->toArray();

        $this->class_sort($toplist);

        $result=Db::query($sql);



        foreach ($result as $key=>$value){
            $result[$key]["update_time"]=date("Y-m-d G:i:s",$result[$key]["update_time"]);
        }

        $this->assign([
            "class"=>$this->class,
            "result"=>$result,
            "sum"=>count($result),
            "stemp"=>$stemp
        ]);
        return view();
    }
    public function edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        $result=$this->modelinformation->where("id",input('id'))->select()->toArray()[0];

         if(!$result){
          return view('error/404');
        }
         $id=["id"=>input('id')];
         $data=input('post.');
         if(!in_array('status',$data)){
            $data=array_merge($data,["status"=>0]);
         }
        if(request()->isPost()) {
            if ($this->modelinformation->allowField(true)->save(
                $data,$id
            )) {
                $this->success("修改成功,请手动刷新后查看！",'close');
            }else{
                $this->error($this->modelinformation->getError());
            }
        }

        $toplist=$this->modeltype->where('lever',1)->select()->toArray();

        $this->class_sort($toplist);

        $this->assign([
            "class"=>$this->class,
            "result"=>$result
        ]);
        return view();
    }

    public function status(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            if($this->modelinformation->where('id',input('id'))->update(['status'=>input('data')])===false){
                return json_encode(["msg"=>$this->modelinformation->getError(),"status"=>500]);
            }else{
                return json_encode(["status"=>200]);
            }

        }
    }
    public function delete(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){

            if($this->modelinformation->where("id in (".input('id').")")->delete()){
                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelinformation->getError(),"status"=>500]);
            }
        }
    }
    public function add(){
        if(!$this->auth->check(request()->controller().'/add',Cookie::get("uid"))){
            return view('./error/authority');
        }
       if(request()->isPost()){

          $data=input('post.');
             if($this->modelinformation->allowField(true)->save(
                $data
             )){
                 $this->success("添加成功，请手动刷新后查看！",'close');
             }else{
              $this->error($this->modelinformation->getError());
             }
       }
        $toplist=$this->modeltype->where('lever',1)->select()->toArray();

        $this->class_sort($toplist);

        $this->assign([
            "class"=>$this->class,
        ]);

        return view();
    }
    public function type_list(){
        $toplist=$this->modeltype->where('lever',1)->select()->toArray();

        $this->class_sort($toplist);
        $this->assign([
            "classlist"=>$this->class,
        ]);
        return view();
    }
    public function type_add(){
        if(request()->isPost()){
            if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
                return json_encode(["msg"=>"权限不足","status"=>403]);
            }
            $data=input("post.");

            $lever=$this->modeltype->where('id',input("pid"))->column("lever");
            if($lever===false){
                return json_encode(["msg"=>$this->modeltype->getError(),"status"=>500]);
            }

            if(count($lever)){
                $data=array_merge($data,["lever"=>$lever[0]+1]);
            }else{
                $data=array_merge($data,["lever"=>1]);
            }


            if($this->modeltype->allowField(true)->validate(true)->save(
               $data
            )){
                return json_encode(["msg"=>"操作成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeltype->getError(),"status"=>500]);
            }

        }

        $toplist=$this->modeltype->where('lever',1)->select()->toArray();

        $this->class_sort($toplist);
        $this->assign([
            "class"=>$this->class,
        ]);
        return view();
    }
    public function type_edit(){
        if(request()->isPost()){
            if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
                return json_encode(["msg"=>"权限不足","status"=>403]);
            }
            $data=input("post.");

                $id=["id"=>input('id')];

                unset($data["id"]);

            $lever=$this->modeltype->where('id',input("pid"))->column("lever");
            if($lever===false){
                return json_encode(["msg"=>$this->modeltype->getError(),"status"=>500]);
            }

            if(count($lever)){
                $data=array_merge($data,["lever"=>$lever[0]+1]);
            }else{
                $data=array_merge($data,["lever"=>1]);
            }


            if($this->modeltype->allowField(true)->validate(true)->save(
                $data,$id
            )){
                return json_encode(["msg"=>"操作成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeltype->getError(),"status"=>500]);
            }

        }
        $result = $this->modeltype->where("id",input('get.')["id"])->select()->toArray()[0];

        $toplist=$this->modeltype->where('lever',1)->select()->toArray();

        $this->class_sort($toplist);
        $this->assign([
            "class"=>$this->class,
            "result"=>$result
        ]);
        return view();
    }
    public function class_sort($toplist,$lever=1){

       foreach ($toplist as $key =>$value) {
           if(is_array($value)) {
                array_push($this->class, array("id"=>$value["id"],"pid"=>$value["pid"],"name"=>"├" . str_repeat("-", ($lever - 1) * 2) . $value["name"]));

               $row = $this->modeltype->where('pid', $value["id"])->select()->toArray();

               if (count($row) != 0) {
                       $this->class_sort($row, $lever + 1);
               }
           }
            }

    }
    public function type_delete(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return json_encode(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){

            $result=$this->modeltype->where("pid",input('id'))->select()->toArray();
            if(count($result)>0){
                return json_encode(["msg"=>"请先删除下级分类","status"=>500]);
            }else{
                if ($result===false){
                    return json_encode(["msg"=>$this->modeltype->getError(),"status"=>500]);
                }
            }
            if($this->modeltype->where("id",input('id'))->delete()){

                return json_encode(["msg"=>"删除成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modeltype->getError(),"status"=>500]);
            }
        }
    }


}