<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19 0019
 * Time: 下午 10:30
 */

namespace app\admin\validate;


use think\Validate;

class Banner extends Validate
{
    protected $rule=[
        ["title","require|max:25","标题是必须的|标题不能超过25个字符"],
        ["url","require|url","链接必须|链接格式不正确"],
        ["sort","number","排序值只能是数字"],
        ["imagesrc","require","图片必须上传"],
        ["starttime","require|date","开始时间必须设置|时间格式不正确"],
        ["stoptime","require|date","结束时间必须设置|时间格式不正确"],
    ];
}