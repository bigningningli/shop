<?php
namespace app\index\controller;
use think\Controller;
use think\Cookie;
use think\Loader;
use think\Request;
use think\Session;
use think\captcha;
use app\index\controller\Common;
use app\admin\model\Column;
class Index extends Common
{
    public $modelcolumn;

    public function init()
    {

    }

    public function index()
    {


        foreach ($this->arr as $key=>$value){
           array_push($this->arr[$key],array_slice($this->index_goodsclass_query($value["id"]),0,6));
        }




        $this->assign([
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
