<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class AccountCate extends Model
{
    //
    protected $table = "account_cate";
    protected $pk = "id";
}
