<?php


namespace app\model;


use think\Model;

class OrdersDeposit extends Model
{
    protected $table = "orders_deposit";
    protected $pk = "id";

    public function amountAccounts() {
        return $this->belongsTo(AmountAccount::class, "amount_account_id", "id");
    }
}
