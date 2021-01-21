<?php


namespace app\admin\service;


use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersFinalPaymentMapper;
use app\mapper\OrdersMainMapper;
use excel\Excel;

class OriginBiService
{
    # 订单状态
    private $status = [
        1 => "未发出",
        2 => "已发出",
        3 => "已交稿",
        4 => "准备退款",
        5 => "已退款",
        6 => "已发全能",
        7 => "已发发单",
        8 => "返修中",
    ];

    /**
     * 来源BI统计数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function originBi($param) {
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["om.create_time", ">=", strtotime($param["range_time"][0])],
                ["om.create_time", "<=", strtotime($param["range_time"][1])],
            ];
        }else {
            $where = [
                ["om.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        $mapper = new OrdersMainMapper();
        # 获取数据
        $originBiData = $mapper->originData($where);
        $tmp = [];
        $countMap = [];
        foreach ($originBiData as $k => $v) {
            $tmp[$v["origin_name"]][] = $v;
            $countMap[$v["origin_name"]] = ($countMap[$v["origin_name"]]??0) + 1;
        }
        # 获取订单金额数据
        # 定金
        $map = [
            ["od.create_time", ">=", $where[0][2]],
            ["od.create_time", "<=", $where[1][2]],
        ];
        $_depositData = $mapper->marketDetailDeposit($map);
        $depositData = processAmount($_depositData, "deposit", "origin_name");

        # 尾款
        $_finalPayment = $mapper->marketDetailFinal($map);
        $finalPaymentData = processAmount($_finalPayment, "final_payment", "origin_name");

        # 退款
        $map = [
            ["od.refund_time", ">=", $where[0][2]],
            ["od.refund_time", "<=", $where[1][2]],
        ];
        $_refund = $mapper->marketDetailRefund($map);
        $refundData = processAmount($_refund, "refund_amount", "origin_name");
        $originAmountData = array_merge($_depositData, $_finalPayment, $_refund);

        # 毛利润
        $mainOrderIds = array_unique(array_merge(array_column($originBiData, "main_order_id"), array_column($originAmountData, "main_order_id")));
        $grossProfitData = $mapper->marketDetailGrossProfit(["om.id" => $mainOrderIds]);
        $grossProfit = [];
        $grossProfitMap = [];
        foreach ($grossProfitData as $k => $v) {
            $grossProfit[$v["origin_name"]] = ($grossProfit[$v["origin_name"]]??0) + ($v["deposit"] + $v["final_payment"]);
            $grossProfitMap[$v["origin_name"]] = ($grossProfitMap[$v["origin_name"]]??0) + ($v["deposit"] + $v["final_payment"])*$v["commission_ratio"];
        }

        $checkFeeMap = [];
        $manuscriptFeeMap = [];
        # 检测费/稿费
        $fee = $mapper->marketDetailFee(["om.id" => $mainOrderIds]);
        foreach ($fee as $k => $v) {
            $checkFeeMap[$v["origin_name"]] = ($checkFeeMap[$v["origin_name"]]??0) + $v["check_fee"];
            $manuscriptFeeMap[$v["origin_name"]] = ($manuscriptFeeMap[$v["origin_name"]]??0) + $v["manuscript_fee"];
        }
        $total = array_sum(array_values($depositData)) + array_sum(array_values($finalPaymentData));
        $retData = [];
        foreach ($tmp as $k => $v) {
            $deposit = $depositData[$k]??0;
            $final = $finalPaymentData[$k]??0;
            $totalAmount = $deposit + $final;
            $grossProfit_ = $grossProfit[$k] - $checkFeeMap[$k] - $manuscriptFeeMap[$k] - $grossProfitMap[$k];
            $item = [
                "origin_name" => $k,
                "origin_id" => $v[0]["origin_id"],
                "total_amount" => $totalAmount,
                "total_count" => $countMap[$k]??0,
                "refund_amount" => $refundData[$k]??0,
                "gross_profit" => floatval(round($grossProfit_, 2)),
                "gross_profit_rate" => ($grossProfit_==0||$grossProfit[$k]==0)?"0%":round(($grossProfit_ / $grossProfit[$k])*100, 2)."%",
                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%",
                "supplier_commission" => floatval(round($grossProfitMap[$k], 2)),
                "deposit" => $deposit,
                "final_payment" => $final,
            ];
            $retData[] = $item;
        }
        $deposit = array_combine(array_column($_depositData, "origin_name"), array_column($_depositData, "origin_id"));
        $final = array_combine(array_column($_finalPayment, "origin_name"), array_column($_finalPayment, "origin_id"));
        $refund = array_combine(array_column($_refund, "origin_name"), array_column($_refund, "origin_id"));
        $tail = array_merge($final, $refund, $deposit);
        foreach ($tail as $k => $v) {
            if (!in_array($k, array_keys($tmp))) {
                $grossProfit_ = $grossProfit[$k] - $checkFeeMap[$k] - $manuscriptFeeMap[$k] - $grossProfitMap[$k];
                $item = [
                    "origin_name" => $k,
                    "origin_id" => $v,
                    "total_amount" => $finalPaymentData[$k]??0 + $depositData[$k]??0,
                    "total_count" => 0,
                    "refund_amount" => $refundData[$k]??0,
                    "gross_profit" => floatval(round($grossProfit_, 2)),
                    "gross_profit_rate" => ($grossProfit_==0||$grossProfit[$k]==0)?"0%":round(($grossProfit_ / $grossProfit[$k])*100, 2)."%",
                    "deal_rate" => $total==0?"0%":round((($finalPaymentData[$k]??0 + $depositData[$k]??0) / $total)*100, 2)."%",
                    "supplier_commission" => floatval(round($grossProfitMap[$k], 2)),
                    "deposit" => 0,
                    "final_payment" => $finalPaymentData[$k]??0,
                ];
                $retData[] = $item;
            }
        }
        $sort = array_column($retData, "total_amount");
        array_multisort($sort, SORT_DESC, $retData);
        $max = $retData[0]["total_amount"]??0;
        foreach ($retData as $k => $v) {
            $retData[$k]["rank"] = $k+1;
            $retData[$k]["champion_ratio"] = floatval(round(($v["total_amount"] / $max) * 100, 2))."%";
        }
        return $retData;
    }


    /**
     * 来源详情
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function originDetailBi($param) {
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $startTime = strtotime($param["range_time"][0]);
            $endTime = strtotime($param["range_time"][1]);
        }else {
            $startTime = strtotime(date("Y-m-d", time()));
            $endTime = time();
        }
        # 获取数据
        $mapper = new OrdersMainMapper();
        $map = [
            ["od.create_time", ">=", $startTime],
            ["od.create_time", "<=", $endTime],
            ["om.origin_id", "=", $param["origin_id"]]
        ];
        # 定金
        $depositData = $mapper->originDetailDeposit($map);
        # 尾款
        $finalPaymentData = $mapper->originDetailFinal($map);
        # 退款
        $map = [
            ["od.refund_time", ">=", $startTime],
            ["od.refund_time", "<=", $endTime],
            ["om.origin_id", "=", $param["origin_id"]]
        ];
        $refundData = $mapper->originDetailRefund($map);

        $data = array_merge($depositData, $finalPaymentData, $refundData);

        $retData = [];
        foreach ($data as $k => $v) {
            $item = [
                "name" => $v["name"],
                "deposit" => isset($v["deposit"])?floatval($v["deposit"]):0,
                "final_payment" => isset($v["final_payment"])?floatval($v["final_payment"]):0,
                "refund_amount" => isset($v["refund_amount"])?floatval($v["refund_amount"]):0,
                "amount_time" => date("Y-m-d H", $v["amount_time"]),
                "order_sn" => $v["order_sn"]
            ];
            $retData[] = $item;
        }
        return $retData;
    }


    /**
     * 对账信息
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function originReconciliation($param) {
        $where = [
            ["om.origin_id", "=", $param["origin_id"]],
            ["od.create_time", ">=", strtotime($param["range_time"][0])],
            ["od.create_time", "<=", strtotime($param["range_time"][1])],
        ];
        # 定金
        $deposit = (new OrdersDepositMapper())->depositWithOrigin($where);
        # 尾款
        $finalPayment = (new OrdersFinalPaymentMapper())->finalPaymentWithOrigin($where);
        $amountData = array_merge($deposit, $finalPayment);
        $data = [];
        foreach ($amountData as $k => $v)
            $data[$v["amount_account_id"]][] = $v;

        $retData = [];
        foreach ($data as $k => $v) {
            $item = [
                "amount_account" => $v[0]["account"],
                "total_amount" => array_sum(array_column($v, "change_amount")),
                "amount_account_id" => $k
            ];
            $retData[] = $item;
        }
        return $retData;
    }


    /**
     * 导出信息
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($param) {
        $data = $this->originBi($param);
        $header = [
            ["来源", "origin_name"],
            ["排名", "rank"],
            ["成交占比", "deal_rate"],
            ["入账", "total_amount"],
            ["毛利润", "gross_profit"],
            ["毛利率", "gross_profit_rate"],
            ["退款", "refund_amount"],
            ["成交单数", "total_count"],
            ["上家分额", "supplier_commission"],
            ["定金", "deposit"],
            ["尾款", "final_payment"],
        ];
        return Excel::exportData($data, $header, "来源数据");
    }

    /**
     * 详情导出
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportDetail($param) {
        $data = $this->originDetailBi($param);
        $header = [
            ["客服", "name"],
            ["定金", "deposit"],
            ["尾款", "final_payment"],
            ["退款", "refund_amount"],
            ["收款/退款时间", "amount_time"],
            ["订单编号", "order_sn"],
        ];
        return Excel::exportData($data, $header, "来源详情数据");
    }

    /**
     * 对账详情导出
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function exportRec($param) {
        $where = [
            ["om.origin_id", "=", $param["origin_id"]],
            ["od.amount_account_id", "=", $param["amount_account_id"]],
            ["od.create_time", ">=", strtotime($param["range_time"][0])],
            ["od.create_time", "<=", strtotime($param["range_time"][1])],
        ];
        # 定金
        $deposit = (new OrdersDepositMapper())->depositRecWithOrderSn($where);
        # 尾款
        $finalPayment = (new OrdersFinalPaymentMapper())->finalPaymentRecWithOrderSn($where);
        $data = array_merge($deposit, $finalPayment);
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["order_create_time"] = date("Y-m-d H:i:s", $v["order_create_time"]);
            $data[$k]["status"] = $this->status[$v["status"]];
        }
        $header = [
            ["接单客服", "customer_name"],
            ["订单编号", "order_sn"],
            ["要求", "require"],
            ["收款金额", "change_amount"],
            ["收款账户", "account"],
            ["收款时间", "create_time"],
            ["订单创建时间", "order_create_time"],
            ["订单状态", "status"],
            ["收款人", "name"],
        ];
        return Excel::exportData($data, $header, "对账详情数据");
    }
}
