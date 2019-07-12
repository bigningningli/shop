<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5 0005
 * Time: 下午 10:43
 */

namespace app\admin\validate;
use think\Validate;

class Type extends Validate
{
    protected $rule=[
        ["name","require|max:25","名称是必须的|名称不能超过25字符"],
        ["pid","number","上级栏目匹配未成功"]
    ];
}