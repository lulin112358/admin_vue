<?php


namespace app\model;


use think\Model;

class OrdersMain extends Model
{
    protected $table = "orders_main";
    protected $pk = "id";

    public function deposits() {
        return $this->hasMany(OrdersDeposit::class, "main_order_id", "id");
    }
}
