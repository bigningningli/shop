<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * User: Administrator
 * Date: 2019/1/16 0016
 * Time: 11:50
 */

namespace app\admin\controller;


use app\admin\model\AuthGroupAccess;
use app\admin\model\AuthMember;
use think\Loader;
use think\Controller;
use think\Session;
use think\View;
use think\Cookie;
use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;
use app\admin\validate\AuthMember as memberv;
class Admin extends Common
{
    public $auth;
    public $modelrule;
    public $modelgroup;
    public $modelgroupaccess;
    public $modelmember;
    public $memberv;
    public $test;
    public function init()
    {
        Loader::import("org/Auth", EXTEND_PATH);
        $this->auth=new \Auth();

        $this->modelrule=new AuthRule();
        $this->modelgroup=new AuthGroup();
        $this->modelgroupaccess=new AuthGroupAccess();
        $this->modelmember=new AuthMember();
        $this->memberv=new memberv();
    }

    public function Index(){
        return view('admin-list');
    }
    public function admin_list(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=null;
        $stemp=null;
        if(input('stemp')==1||Session::get('table')!="auth_member"){
           Session::clear();
        }
        if((Session::get("table")=="auth_member")||(request()->isPost()&&(input('datemin')!=""||input('datemax')!=""||input('selectuser')!=""))){
            if(request()->isPost()) {
                Session::set("datemin", input('datemin'));
                Session::set("datemax", input('datemax').' 24:00:00');
                Session::set("select", input("selectuser"));
                Session::set("table","auth_member");
            }
            $Tools=new Tools();
            $result=$Tools->select_condition(Session::get('datemin'),Session::get('datemax'),Session::get('select'),Session::get("table"));
            $stemp=1;
        }else {
            $result = $this->modelmember->select()->toArray();

        }


        foreach ($result as $key=>$val){
           $garray=$this->auth->getGroups($val["id"]);
            $result[$key]=array_merge($val,["groupname"=>""]);
            $groupname="";
            foreach ($garray as $v){
                $groupname.=$this->modelgroup->where("id",$v["group_id"])->find()["title"].',';
            }
            $result[$key]["groupname"]=$groupname;
        }
        $this->assign(
            [
                "member"=>$result,
                "stemp"=>$stemp,
                "sum"=>count($result)
            ]

        );
        return  view();
    }
    public function admin_edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()) {
            $ucheck=$this->modelmember->where("username",input('username'))->select()->toArray();
            if(count($ucheck)&&$ucheck[0]["id"]!=input('id')){
                return json_encode(["msg"=>"该用户名已使用！","status"=>500]);
            }
            $id = ["id" => input('id')];

            $data=input('post.');
            //留坑---->
           if(strlen(input('userpassword'))==0&&strlen(input('userpassword'))==strlen(input('usernamepassword_confirm'))){
                $data["userpassword"]="-1";
                $data["userpassword_confirm"]="-1";
                $mcheck = $this->memberv->check($data);
                unset($data["userpassword"]);

            }else{
               $mcheck = $this->memberv->check($data);
               $data["userpassword"]=md5($data["userpassword"]);
           }
           //<----
           unset($data["userpassword_confirm"]);
           unset($data["id"]);
           $gid=$data["adminRole"];
           unset($data["adminRole"]);
            if ($mcheck) {
                if ($this->modelmember->allowField(true)->save(
                  $data,
                    $id
                )!==false) {
                    if($this->modelgroupaccess->save(
                        ["group_id"=>$gid],
                        ["uid"=>input('id')]

                    )!==false) {
                        return json_encode(["msg" => "修改成功", "status" => 200]);
                    }else{
                        return json_encode(["msg"=>$this->modelgroupaccess->getError(),"status"=>500]);
                    }
                } else {
                    return json_encode(["msg" => $this->modelmember->getError(), "status" => 500]);
                }
            } else {
                return json_encode(["msg" => $this->memberv->getError(), "status" => 500]);
            }
        }else {
            $info=$this->auth->getUserInfo(input('id'));
            $group=$this->auth->getGroups(input('id'))[0]["group_id"];
            $glist=$this->modelgroup->field('id,title')->select();
            $this->assign([
                "info"=>$info,
                "group"=>$group,
                "glist"=>$glist
            ]);
        }
       return view();

    }
    public function  admin_add(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()) {
            if($this->modelmember->where("username",input('username'))->count()){
                return json(["msg"=>"该用户名已使用！","status"=>500]);
            }
            $data=input('post.');

            $mcheck = $this->memberv->check($data);
            $data["userpassword"]=md5($data["userpassword"]);
            unset($data["userpassword_confirm"]);

            $gid=$data["adminRole"];
            unset($data["adminRole"]);
            if ($mcheck) {
                if ($this->modelmember->allowField(true)->save(
                        $data
                )) {
                    if($this->modelgroupaccess->save(
                            [
                                "group_id"=>$gid,
                                "uid"=>$this->modelmember->id
                            ]
                        )) {
                        return json(["msg" => "添加成功", "status" => 200]);
                    }else{
                        return json(["msg"=>$this->modelgroupaccess->getError(),"status"=>500]);
                    }
                } else {
                    return json(["msg" => $this->modelmember->getError(), "status" => 500]);
                }
            } else {
                return json(["msg" => $this->modelmember->getError(), "status" => 500]);
            }
        }else {

            $glist=$this->modelgroup->field('id,title')->select();

            $this->assign([

                "glist"=>$glist
            ]);
        }
        return view();
    }
    public function admin_status(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(input('id')==Cookie::get('uid')){
            return json(["msg"=>"禁止操作自身","status"=>500]);
        }
        if(request()->isPost()){
            if($this->modelmember->save(
                [ 'status'=>input("status")],
                ['id'=>input("id")]
            )!==false){
                return json(["status"=>200]);
            }else{
              return json(["msg"=>$this->modelmember->getError(),"status"=>500]);
            }
        }
    }
    public function admin_delete(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            $data=explode(",",input('post.')["id"]);
            if(in_array(Cookie::get('uid'),$data)){
                return json(["msg"=>"禁止删除自身","status"=>500]);
            }

            $this->modelmember->startTrans();
            if($this->modelmember->where("id in (".input('post.')['id'].")")->delete()===false){
                return json(["msg"=>$this->modelmember->getError(),"status"=>200]);
            }else{
                if($this->modelgroupaccess->where("uid in (".input('post.')['id'].")")->delete()===false){
                    $this->modelmember->rollback();
                    return json(["msg"=>$this->modelgroupaccess->getError(),"status"=>200]);
                }else{
                    $this->modelmember->commit();
                    return json(["msg"=>"删除成功","status"=>200]);
                }
            }


        }
    }
    public function  admin_role(){
        if(!$this->auth->check(request()->controller().'/see',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $list=$this->modelgroup->paginate(6);
        $page=$list->render();
        $list=$list->toArray()["data"];
        foreach ($list as $key=>$val){
            $l=$this->auth->Group_getUsers($val["id"]);
            $list[$key]=array_merge($list[$key],array("username"=>''));
               foreach ($l as $v){
               $list[$key]["username"]=$list[$key]["username"].$this->auth->getUserInfo($v['uid'])["username"].',';
               }
        }
        $sum=$this->modelgroup->count();
        $this->assign([
            "list"=>$list,
            "sum"=>$sum,
            "page"=>$page
            ]);
        return view();
    }
    public function admin_role_add(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()) {
            $data=input('post.');
            $rules="";
            foreach ($data as $key => $val){
                if(!(strstr($key,'-')===false)){
                    $rules.=$val.',';
                }
            }

                if ($this->modelgroup->allowField(true)->validate(true)->save(
                    [
                        'title' => $data["roleName"],
                        'description' => $data["roleDescription"],
                        'rules' => $rules
                    ]
                )) {
                    return json(["msg"=>"添加成功","status"=>200]);
                }else{
                    return json(["msg"=>$this->modelgroup->getError(),"status"=>500]);
                }

        }

        return view();
    }
    public function admin_role_edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return view('./error/authority');
        }
        if(request()->isPost()){
            $data=input('post.');
            $rules="";
            foreach ($data as $key => $val){
                if(!(strstr($key,'-')===false)){
                    $rules.=$val.',';
                }
            }

            $id=["id"=>$data["id"]];

            if($this->modelgroup->allowField(true)->validate(true)->save(
                [
                    "title"=>$data["roleName"],
                    "description"=>$data["roleDescription"],
                    "rules"=>$rules
                ],
                $id
            )){
                    return json(["msg"=>"修改成功","status"=>200]);
            }else{
                    return json(["msg"=>$this->modelgroup->getError(),"status"=>500]);
            }

        }else{
            $result=$this->modelgroup->where("id",input('id'))->field('title,description,rules')->select()->toArray()[0];
            $arr=explode(",",$result["rules"]);
            array_pop($arr);
            unset($result["rules"]);
        }
        $this->assign([
            "result"=>$result,
            "rlist"=>$arr
        ]);
        return view();


    }
    public function admin_role_delete(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        $data=explode(",",input('post.')["id"]);
        if(in_array($this->auth->getGroups(Cookie::get('uid'))[0]["group_id"],$data)){
            return json(["msg"=>"禁止删除登录的角色","status"=>500]);
        }

        if(request()->isPost()){
            $this->modelgroup->startTrans();

            if($this->modelgroup->where('id in ('.input("post.")["id"].')')->delete()===false){

                return json(["msg"=>$this->modelgroup->getError(),"status"=>500]);
            }else{
                if($this->modelgroupaccess->where('group_id in ('.input("post.")["id"].')')->delete()===false){
                    $this->modelgroup->rollback();
                    return json(["msg"=>$this->modelgroupaccess->getError(),"status"=>500]);
                }else{
                    $this->modelgroup->commit();
                    return json(["msg"=>"删除成功","status"=>200]);
                }
            }

        }
    }

}