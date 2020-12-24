<?php


namespace app\admin\service;


use app\mapper\OrdersFinalPaymentMapper;
use think\facade\Db;

class OrdersFinalPaymentService extends BaseService
{
    protected $mapper = OrdersFinalPaymentMapper::class;

    /**
     * 获取尾款列表
     *
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function finalPayment($data) {
        return (new OrdersFinalPaymentMapper())->finalPayment($data);
    }


    /**
     * 修改尾款
     * @param $data
     * @return bool
     */
    public function updateFinalPayment($data) {
        Db::startTrans();
        try {
            $info = $this->findBy(["main_order_id" => $data["main_order_id"], "status" => 1], "final_payment");
            if (!$info) {
                $change = $data["value"];
                $final_payment = $data["value"];
            }else {
                $change = $data["value"] - $info["final_payment"];
                $final_payment = $info["final_payment"] + $change;
            }
            $res = $this->updateWhere(["main_order_id" => $data["main_order_id"]], ["status" => 0]);
            if ($res === false)
                throw new \Exception("失败");
            $finalData = [
                "main_order_id" => $data["main_order_id"],
                "change_amount" => $change,
                "final_payment" => $final_payment,
                "amount_account_id" => $data["amount_account_id"],
                "payee_id" => request()->uid,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res =  $this->add($finalData);
            if (!$res)
                throw new \Exception("失败!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
