<?php


namespace app\admin\service;


use app\mapper\OrdersMapper;
use app\mapper\SettlementLogMapper;
use app\model\Orders;
use excel\Excel;
use think\facade\Db;

class SettlementLogService extends BaseService
{
    protected $mapper = SettlementLogMapper::class;

    /**
     * 获取全部结算记录
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function settlementLogs($param) {
        $where = [];
        if (isset($param["search_key"]) && !empty($param["search_key"])) {
            $where[] = ["o.order_sn|u.name", "like", "%{$param['search_key']}%"];
        }
        if (isset($param["create_time"]) && !empty($param["create_time"])) {
            $where[] = ["sl.create_time", ">=", strtotime($param["create_time"][0])];
            $where[] = ["sl.create_time", "<=", strtotime($param["create_time"][1])];
        }
        $data = (new SettlementLogMapper())->settlementLogs($where);
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["settlement_fee"] = floatval($v["settlement_fee"]);
        }
        return $data;
    }

    /**
     * 导出结算记录
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($param) {
        $_data = $this->settlementLogs($param);
        $header = [
            ["订单编号", "order_sn"],
            ["结算金额", "settlement_fee"],
            ["结算人", "settlement_user_name"],
            ["结算时间", "create_time"]
        ];
        return Excel::exportData($_data, $header, "结算记录数据");
    }

    /**
     * 全部结算
     * @param $param
     * @return bool
     */
    public function settlementAll($param, $can = false) {
        if ($can) {
            $orders = (new OrdersMapper())->selectBy([["engineer_id", "=", $param["engineer_id"]], ["is_check", "=", 1], ["is_clear", "=", 0]], "manuscript_fee, settlemented, deduction, id");
        }else {
            $orders = (new OrdersMapper())->selectBy(["id" => $param["order_id"]], "manuscript_fee, settlemented, deduction, id");
        }
        $updateData = [];
        $insertData = [];
        $settlementFee = $param["settlement_fee"];
        foreach ($orders as $k => $v) {
            $updateData[] = [
                "settlemented" => $v["manuscript_fee"] - $v["deduction"],
                "is_clear" => 1,
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
                $updateData[$k]["is_clear"] = 0;
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
