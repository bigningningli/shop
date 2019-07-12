<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13 0013
 * Time: 20:54
 */

namespace app\admin\validate;

use think\Validate;
class AuthRule extends Validate
{
    protected $rule=[
        ['name','require|max:80','请创建对应规则！|对应规则不符合规范，请重新设置！'],
        ['title','require|max:20','请输入规则名称！|名称长度不能超过20字节,请重试！'],
        ['condition','max:100','请将条件长度控制在100字节以内！']
    ];
}