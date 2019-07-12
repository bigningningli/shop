<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/1 0001
 * Time: 上午 11:19
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
class Goods extends Model
{
    use SoftDelete;
    protected $autoWriteTimestamp=true;
    protected $deleteTime='delete_time';
}