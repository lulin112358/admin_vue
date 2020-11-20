<?php


namespace app\model;


use think\Model;

class OrdersFinalPayment extends Model
{
    protected $table = "orders_final_payment";
    protected $pk = "id";

    public function amountAccounts() {
        return $this->belongsTo(AmountAccount::class, "amount_account_id", "id");
    }
}
