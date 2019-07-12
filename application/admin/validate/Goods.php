<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/2 0002
 * Time: 下午 8:00
 */

namespace app\admin\validate;
use think\Validate;

class Goods extends Validate
{
    protected $rule=[
        ['title','require|max:60','名称是必须的|名称不能超过60字符'],
        ['original_price','float','原价必须为数字'],
        ['price','float','销售价格必须为数字'],
        ['inventory','number','库存必须为整数'],
        ['buy_min_number','number','起购数量必须为整数'],
        ['buy_max_number','number','最大购买数量必须为整数'],
        ['status','number|between:0,1','无效的状态，请重新选择|无效的状态，请重新选择'],
        ['is_home_recommended','number|between:0,1','无效的状态，请重新选择|无效的状态，请重新选择'],
        ['images','require','展示图片必须上传'],
        ['weight','require|number',"请输入重量|重量必须为数字"],
        ['inventory_unit','require','请选择库存单位']

        ];
}