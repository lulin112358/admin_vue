<?php


namespace app\admin\service;


use app\mapper\OrdersDepositMapper;
use think\facade\Db;

class OrdersDepositService extends BaseService
{
    protected $mapper = OrdersDepositMapper::class;


    /**
     * 获取定金列表
     *
     * @param $data
     * @return array
     */
    public function deposit($data) {
        return (new OrdersDepositMapper())->deposit($data);
    }

    /**
     * 更新定金
     *
     * @param $data
     * @return bool
     */
    public function updateDeposit($data) {
        Db::startTrans();
        try {
            $info = $this->findBy(["main_order_id" => $data["main_order_id"], "status" => 1], "deposit");
            if (!$info) {
                $change = $data["value"];
                $deposit = $data["value"];
            }else {
                $change = $data["value"] - $info["deposit"];
                $deposit = $info["deposit"] + $change;
            }
            $res = $this->updateWhere(["main_order_id" => $data["main_order_id"]], ["status" => 0]);
            if ($res === false)
                throw new \Exception("失败");
            $depositData = [
                "main_order_id" => $data["main_order_id"],
                "change_deposit" => $change,
                "deposit" => $deposit,
                "amount_account_id" => $data["amount_account_id"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res =  $this->add($depositData);
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
