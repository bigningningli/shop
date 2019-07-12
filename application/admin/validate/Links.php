<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/20 0020
 * Time: 下午 8:33
 */

namespace app\admin\validate;

use think\Validate;
class Links extends Validate
{
    protected $rule=[
        ['name','require|max:30','名称是必须的|名称不能超过30字符'],
        ['url','require|url','链接必填|请输入正确格式的链接'],
        ['sort','require|number','请选择排序位置|排序值必须为数字'],
        ['status','number|between:0,1','无效的状态，请重新选择|无效的状态，请重新选择'],
        ['description','max:255','描述最大长度不能超过255字节'],
    ];
}