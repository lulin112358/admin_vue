<?php


namespace app\model;


use think\Model;

class Orders extends Model
{
    protected $table = "orders";

    /**
     * 定义关联关系
     *
     * @return \think\model\relation\BelongsTo
     */
    public function engineers() {
        return $this->belongsTo(Engineer::class, "engineer_id", "id");
    }
}
