<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25 0025
 * Time: 上午 11:26
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
class Test extends Model
{
    use SoftDelete;
    protected $deleteTime="delete_time";
}