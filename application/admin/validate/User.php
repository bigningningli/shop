<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/28 0028
 * Time: 下午 3:32
 */

namespace app\admin\validate;
use think\Validate;

class User extends Validate
{
    protected $rule=[
        ["name","max:30","用户名必须设置|用户名不能超过30字符"],
        ["password","max:20|confirm","用户密码不能超过20字节|两次输入密码不一致!"],
        ['sex','between:0,1',"性别非法"],
        ['phone','max:11','手机号长度不能超过11位!'],
        ['email','email','邮箱格式不正确！'],
        ["birthday","date",'日期格式错误'],
        ["location","max:30","所在地区超出长度限制"],
        ["pay","number|max:6|min:6|confirm","支付密码必须为数字|长度不正确|两次输入密码不一致"]
    ];
}