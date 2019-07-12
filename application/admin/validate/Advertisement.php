<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/9 0009
 * Time: 下午 10:03
 */

namespace app\admin\validate;

use think\Validate;
class Advertisement extends Validate
{
    protected $rule=[
        ["title","require|max:25","标题是必须的|标题不能超过25个字符"],
        ["column","require|number|between:1,8","所处位置为必选项|位置选择错误请重新选择"],
        ["sort","number","排序值只能是数字"],
        ["description","max:200","描述不能超过200字符"],
        ["starttime","require|date","开始时间必须设置|时间格式不正确"],
        ["stoptime","require|date","结束时间必须设置|时间格式不正确"],
    ];
}