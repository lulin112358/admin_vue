<?php


namespace app\admin\service;


use app\mapper\OrdersMapper;
use app\mapper\SettlementLogMapper;
use app\model\Orders;
use think\facade\Db;

class SettlementLogService extends BaseService
{
    protected $mapper = SettlementLogMapper::class;

    /**
     * 全部结算
     * @param $param
     * @return bool
     */
    public function settlementAll($param) {
        $orders = (new OrdersMapper())->selectBy(["id" => $param["order_id"]], "manuscript_fee, settlemented, deduction, id");
        $updateData = [];
        $insertData = [];
        $settlementFee = $param["settlement_fee"];
        foreach ($orders as $k => $v) {
            $updateData[] = [
                "settlemented" => $v["manuscript_fee"] - $v["deduction"],
                "id" => $v["id"]
            ];
            $insertData[] = [
                "order_id" => $v["id"],
                "settlement_fee" => $v["manuscript_fee"] - $v["settlemented"] - $v["deduction"],
                "settlement_user" => request()->uid,
                "create_time" => time()
            ];
            $settlementFee = $settlementFee - ($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            if ($settlementFee <= 0) {
                $insertData[$k]["settlement_fee"] = $settlementFee + ($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
                $updateData[$k]["settlemented"] = $v["settlemented"] + $settlementFee + ($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
                break;
            }
        }
        Db::startTrans();
        try {
            $res = (new Orders())->saveAll($updateData);
            if ($res === false)
                throw new \Exception("结算失败");

            $res = (new SettlementLogMapper())->addAll($insertData);
            if (!$res)
                throw new \Exception("结算失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
