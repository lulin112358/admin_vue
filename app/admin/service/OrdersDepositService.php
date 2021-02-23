<?php


namespace app\admin\service;


use app\mapper\OrdersAccountMapper;
use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersFinalPaymentMapper;
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
     * 获取收款记录
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function paymentLog($data) {
        $deposit = (new OrdersDepositMapper())->deposit($data);
        $finalPayment = (new OrdersFinalPaymentMapper())->finalPayment($data);
        foreach ($deposit as $k => $v) {
            $deposit[$k]["type"] = "定金";
            $deposit[$k]["type_id"] = 1;
        }
        foreach ($finalPayment as $k => $v) {
            $finalPayment[$k]["type"] = "尾款";
            $finalPayment[$k]["type_id"] = 2;
        }
        return array_merge($deposit, $finalPayment);
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
                "payee_id" => request()->uid,
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

    /**
     * 修改收款账号
     * @param $param
     * @return mixed
     */
    public function updateDepositAccount($param) {
        if ($param["type_id"] == 1) {
            return $this->updateWhere(["id" => $param["id"]], ["amount_account_id" => $param["amount_account_id"], "update_time" => time()]);
        }else{
            return (new OrdersFinalPaymentMapper())->updateWhere(["id" => $param["id"]], ["amount_account_id" => $param["amount_account_id"], "update_time" => time()]);
        }
    }

    /**
     * 获取用户今日对账信息
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userPaymentLogByDay() {
        $where = [
            ["od.payee_id", "=", request()->uid],
            ["od.is_check", "=", 0],
            ["od.create_time", "<=", time()]
        ];
        $data = [];
        # 获取接单账号信息
        $account = (new OrdersAccountMapper())->ordersAccount();
        foreach ($account as $k => $v) {
            $account[$k]["account_info"] = $v["nickname"]."/".$v["account"];
        }
        $accountMap = array_combine(array_column($account, "id"), array_column($account, "account_info"));
        $deposit = (new OrdersDepositMapper())->depositRecWithOrderSn($where);
        $finalPayment = (new OrdersFinalPaymentMapper())->finalPaymentRecWithOrderSn($where);
        foreach ($deposit as $k => $v) {
            $v["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $v["type"] = "定金";
            $v["order_account"] = $accountMap[$v["order_account_id"]];
            $data[] = $v;
        }
        foreach ($finalPayment as $k => $v) {
            $v["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $v["type"] = "尾款";
            $v["order_account"] = $accountMap[$v["order_account_id"]];
            $data[] = $v;
        }
        return $data;
    }
}
