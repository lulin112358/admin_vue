<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class Auth extends Model
{
    //
    protected $table = "auth";
    protected $pk = "id";
}
