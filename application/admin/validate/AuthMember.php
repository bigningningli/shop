<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27 0027
 * Time: 23:52
 */

namespace app\admin\validate;

use think\Validate;
class AuthMember extends Validate
{
    protected $rule=[
        ['username','require|max:50','用户名不能为空|用户名长度不能超过50字节'],
        ['userpassword','require|max:20|confirm','用户密码不能为空|用户密码不能超过20字节|两次输入密码不一致!'],
        ['phone','max:11','手机号长度不能超过11位!'],
        ['email','email','邮箱格式不正确！']
    ];
}