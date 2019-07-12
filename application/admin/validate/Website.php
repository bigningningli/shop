<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27 0027
 * Time: 23:52
 */

namespace app\admin\validate;

use think\Validate;
class Website extends Validate
{
    protected $rule=[
        ['title','require|max:50','网站标题是必须的|标题最大长度不能超过50字节'],
        ['description','max:160','描述最大长度不能超过160字节'],
        ['uploadfile','max:100','上传目录长度超出限制，请控制在100字节之内'],
        ['copyright','max:50','版权信息长度超出限制，请控制在50个字节之内'],
        ['icp','max:60','icp长度不能超过60字节'],
        ['umax','max:11','用户限制失败次数长度超出，请输入一个更小的值方便我们存储'],
        ['amax','max:11','后台限制失败次数长度超出，请输入一个更小的值方便我们存储']
    ];
}