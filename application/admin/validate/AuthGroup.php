<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13 0013
 * Time: 16:46
 */

namespace app\admin\validate;

use think\Validate;
class AuthGroup extends Validate
{
    protected $rule=[
        ['title','require|max:100','角色名称必须！|角色最大字符不能超过100'],
        ['roledescription','max:160','描述长度不能超过160字节']
    ];
}