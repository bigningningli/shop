<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13 0013
 * Time: 16:27
 */

namespace app\admin\controller;
use app\admin\model\Test as modeltest;
use app\index\controller\Common;
class Test extends Common
{
    public $n;
    public function init(){

    }
    public function index(){

//             return view();

    }
    public function vcode(){
        if(request()->isPost())
        {

            dump(input('post.'));
        }
return view();
    }

    /**

     * 先生成类似下面的形式的数据

     * [

     *  'id'=>1,

     *  'pid'=>0,

     *  'items'=>[

     *     [
     *      'id'=>2,

     *      'pid'=>'1'
            ],
     *
     *
     *      [
     *      'id'=>3,

     *      'pid'=>'1'
     *
     *      ]
     *       。。。

     *  ]

     * ]*/















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
                $options[$this->n++] = ["name"=>$value['name'],"level"=>$value['level']];


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

}