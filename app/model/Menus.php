<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class Menus extends Model
{
    protected $table = 'menus';
    protected $pk = 'id';
}
