<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/29 0029
 * Time: 下午 3:27
 */

namespace app\admin\model;

use think\Db;
use think\Model;
class Cart extends Model
{
    protected $autoWriteTimestamp=true;
    public function old_number($gid,$cid){
        return Db::name('cart')->field('sum(num) as num')->group('gid,cid')->having('gid = '.$gid.'&&cid='.$cid)->select()[0]['num'];
    }
}