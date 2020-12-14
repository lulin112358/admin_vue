<?php


namespace app\model;


use think\Model;

class UserExtend extends Model
{
    protected $table = "user_extend";
    protected $pk = "id";

    /**
     * 定义关联关系
     * @return \think\model\relation\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    /**
     * 定义学校关联关系
     * @return \think\model\relation\BelongsTo
     */
    public function school() {
        return $this->belongsTo(School::class, "school_id", "id");
    }

    /**
     * 定义学历关联关系
     * @return \think\model\relation\BelongsTo
     */
    public function degree() {
        return $this->belongsTo(Degree::class, "degree_id", "id");
    }
}
