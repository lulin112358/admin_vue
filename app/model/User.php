<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class User extends Model
{
    //
    protected $table = 'user';
    protected $pk = 'id';

    /**
     * 关联关系
     * @return \think\model\relation\BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany(Role::class, UserRole::class, "role_id", "user_id");
    }
}
