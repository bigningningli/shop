<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/28 0028
 * Time: 上午 12:41
 */

namespace app\admin\controller;
use app\admin\model\AuthMember;
use think\Controller;
use think\Db;

class Tools extends Controller
{
    public $modelmember;
    public function _initialize()
    {
//        parent::_initialize(); // TODO: Change the autogenerated stub
    $this->modelmember=new AuthMember();
    }

    public function select_condition($min,$max,$select,$table,$add=""){
        $result=null;
        $min=strtotime($min);
        $max=strtotime($max);

        $sql = "select * from think_" . $table . " where ";

        if($min!=""){
            $sql.="create_time>=".$min;
        }
        if($max!=""){
            if($min!=""){
                $sql.=" and ";
            }
            $sql.="create_time<=".$max;
        }
        if($select!=""){
            if($min!=""||$max!=""){
                $sql.=" and ";
            }
            switch ($table){
                case "auth_member": $sql.="username='".$select."'";break;
                case "column": $sql.="name='".$select."'";break;
            }


        }
        $sql.=$add;
        $result=Db::query($sql);
        foreach ($result as $key=>$value){
            $result[$key]["create_time"]=date("Y-m-d G:i:s",$value["create_time"]);
        }

        return $result;
    }
    public function level(){

    }
    public function close(){
        return view('./include/close');
    }
}