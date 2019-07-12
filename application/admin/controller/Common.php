<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27 0027
 * Time: 下午 1:47
 */

namespace app\admin\controller;
use think\Controller;
use think\Cookie;
use think\Loader;
use think\Session;
use app\admin\model\Website;
use app\admin\model\Column as modelcolumn;
use app\admin\model\Banner as modelbanner;
use app\admin\validate\Banner as valbanner;
use app\admin\validate\Column as valcolumn;
use app\admin\model\Links as modellinks;
use app\admin\model\Region as modelregion;
use app\admin\model\User as modeluser;
use app\admin\validate\User as validateuser;
use app\admin\model\GoodsCategory as modelgoodscategory;
use app\admin\model\Goods as modelgoods;
use app\admin\model\Order as modelorder;
use app\admin\model\Orderdetails as modelorderdetails;
use app\admin\model\Refund as modelrefund;
use app\admin\model\ExpressCompany as modelec;
class Common extends Controller
{
    protected $auth;
    protected $arr;
    protected $modelcolumn;
    protected $modelbanner;
    protected $modellinks;
    protected $modelwebsite;
    protected $webuploader;
    protected $modelregion;
    protected $regionarr;
    protected $regionindex;
    protected $regionclass;
    protected $modeluser;
    protected $validateuser;
    protected $modelgoodscategory;
    protected $goodscategoryarr;
    protected $goodscategoryclass;
    protected $modelgoods;
    protected $unitarr;
    protected $modelorder;
    protected $modelorderdetails;
    protected $modelrefund;
    protected $modelec;
    protected function _initialize()
    {
        Loader::import("org/Auth",EXTEND_PATH);
       $this->auth=new \Auth();
//        if(!$this->auth->check(request()->controller().'/see',Cookie::get("uid"))){
//            $this->error("你没有访问权限！",'tools/close');
//        }
        $this->init();
        $this->regionindex=0;
        $website=Website::get(["status"=>1]);
        define("__TITLE__",$website->title);
        define("__COPYRIGHT__",$website->copyright);
        define("__UPLOAD__",$website->uploadfile);
        define("__ICP__",$website->icp);
        define("__AMAX__",$website->amax);
        $this->webuploader=request()->root()."/".__UPLOAD__."/webuploader/images/";
        $this->modelcolumn=new modelcolumn();
        $this->modelbanner=new modelbanner();
        $this->modellinks=new modellinks();
        $this->modelwebsite=new Website();
        $this->modelrefund=new modelrefund();
        $this->modeluser=new modeluser();
        $this->modelec=new modelec();
        $this->modelregion = new modelregion();
        $this->validateuser=new validateuser();
        $this->modelorder=new modelorder();
        $this->modelorderdetails=new modelorderdetails();
        $this->modelgoodscategory=new modelgoodscategory();
        $this->arr=$this->modelgoodscategory->field('id,pid,name,level')->select()->toArray();
        $this->arr=$this->getTree($this->arr);
        $this->goodscategoryarr=$this->formatTree($this->arr);
        $this->goodscategoryclass=$this->goodscategoryarr;
        foreach ($this->goodscategoryclass as $key=>$value){
            $this->goodscategoryclass[$key]["name"]="|-".str_repeat("--",($value["level"]-1)*2).$value["name"];
        }
        $this->arr = $this->modelregion->field('id,pid,name,level')->select()->toArray();
        $this->arr=$this->getTree($this->arr);
        $this->regionarr=$this->formatTree($this->arr);
        $this->regionclass=$this->regionarr;
        foreach ($this->regionclass as $key=>$value){
            $this->regionclass[$key]["name"]="|-".str_repeat("--",($value["level"]-1)*2).$value["name"];
        }
        $this->modelgoods=new modelgoods();
        $this->unitarr=array("件","斤","KG","吨","套");
    }

    public function select_condition($min,$max,$select,$table,$add=""){
        $result=null;
        $min=strtotime($min);
        $max=strtotime($max);
        $sql="select * from think_".$table." where ";
        if($min!=""){

            $sql.="create_time>=".$min;
        }
        if($max!=""){
            if(strpos($sql,"create_time")!==false){
                $sql.=" and ";
            }
            $sql.="create_time<=".$max;
        }
        if($select!=""){
            if(strpos($sql,"create_time")!==false){
                $sql.=" and ";
            }
            if($table=="auth_member")
                $sql.="username='".$select."'";
            else{
                $sql.="name='".$select."'";
            }
        }
        $sql.=$add;
        $result=Db::query($sql);
        foreach ($result as $key=>$value){
            $result[$key]["create_time"]=date("Y-m-d G:i:s",$value["create_time"]);
        }
//        if($selectuser==""){
//            if($min!=""&&$max==""){
//                $result=$this->modelmember->where('create_time','>=',$min)->paginate(6);
//
//            }
//            if($min==""&&$max!=""){
//                $result=$this->modelmember->where('create_time','<=',$max)->paginate(6);
//
//            }
//            if($min!=""&&$max!="") {
//                $result = $this->modelmember->where('create_time', 'between', $min . ',' . $max)->paginate(6);
//
//            }
//        }else{
//            if($min!=""&&$max==""){
//                $result=$this->modelmember->where('create_time','>=',$min)->where('username',$selectuser)->paginate(6);
//
//            }
//            if($min!=""&&$max==""){
//                $result=$this->modelmember->where('create_time','<=',$max)->where('username',$selectuser)->paginate(6);
//
//            }
//            if($min!=""&&$max!="") {
//                $result = $this->modelmember->where('create_time', 'between', $min . ',' . $max)->where('username',$selectuser)->paginate(6);
//
//            }
//        }
        return $result;
    }

    public function class_sort($arr,$pid=0){
            static $list=array();
            foreach ($arr as $value){
                if($value["pid"]==$pid){
                    $list[]=$value;
                    $this->class_sort($arr,$value["id"]);
                }
            }
            return $list;
    }

    function getTree($list, $pid = 0)

    {
        $tree = [];
        if (!empty($list)) {        //先修改为以id为下标的列表

            $newList = [];
            foreach ($list as $k => $v) {
                $newList[$v['id']] = $v;

            }        //然后开始组装成特殊格式

            foreach ($newList as $value) {
                if ($pid == $value['pid']) {//先取出顶级

                    $tree[] =&$newList[$value['id']];

                } elseif (isset($newList[$value['pid']])) {//再判定非顶级的pid是否存在，如果存在，则再pid所在的数组下面加入一个字段items，来将本身存进去

                    $newList[$value['pid']]['items'][] = &$newList[$value['id']];

                }

            }

        }    return $tree;

    }

    function formatTree($tree)
    {

        $options = [];

        if (!empty($tree)) {
            foreach ($tree as $key => $value) {

                //php中文网原文为 $options[$value['id']] = $value['name'];到底怎么写可以自己思考一下
                //$this->n 为函数外定义的变量初始化为0
                $options[$this->regionindex++] = ["id"=>$value["id"],"pid"=>$value["pid"],"name"=>$value['name'],"level"=>$value['level']];


                if (isset($value['items'])) {//查询是否有子节点

                    $optionsTmp = $this->formatTree($value['items']);
                    if (!empty($optionsTmp)) {
                        $options = array_merge($options, $optionsTmp);

                    }

                }

            }

        }
        return $options;

    }
    public function imgdel($a,$update){
       $init=explode(",",$a);
       $temp=$init;
       if(array_pop($temp)==""){
           array_pop($init);
       }

      foreach ($init as $value){
            if(strpos($update,$value)===false){
                if(!unlink($_SERVER["DOCUMENT_ROOT"].$this->webuploader.$value)){
                    return false;
                }
            }
      }

      return true;
    }

}