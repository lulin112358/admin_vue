<?php


namespace app\admin\service;


use app\mapper\OrdersMainMapper;

class MarketBiService
{
    /**
     * 市场人员Bi统计数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function marketUserBi($param) {
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
        # 获取市场专员订单数量数据
        $mapper = new OrdersMainMapper();
        $marketUserData = $mapper->marketUserBiData($where);
        $tmp = [];
        $countMap = [];
        foreach ($marketUserData as $k => $v) {
            $tmp[$v["market_user_name"]][] = $v;
            $countMap[$v["market_user_name"]] = ($countMap[$v["market_user_name"]]??0) + 1;
        }

        # 定金
        $map = [
            ["od.create_time", ">=", $where[0][2]],
            ["od.create_time", "<=", $where[1][2]],
        ];
        $_depositData = $mapper->marketDeposit($map);
        $deposit = collect($_depositData)->where("is_split", "=", 0)->toArray();
        $depositData = processAmount($deposit, "deposit", "market_user_name");

        # 尾款
        $_finalPayment = $mapper->marketFinal($map);
        $finalPaymentData = processAmount($_finalPayment, "final_payment", "market_user_name");

        # 退款
        $map = [
            ["od.refund_time", ">=", $where[0][2]],
            ["od.refund_time", "<=", $where[1][2]],
        ];
        $_refund = $mapper->marketRefund($map);
        $refundData = processAmount($_refund, "refund_amount", "market_user_name");

        $marketBiAmountData = array_merge($_depositData, $_finalPayment, $_refund);
        # 毛利润
        $mainOrderIds = array_unique(array_merge(array_column($marketUserData, "main_order_id"), array_column($marketBiAmountData, "main_order_id")));
        $grossProfitData = $mapper->marketGrossProfit(["om.id" => $mainOrderIds]);
        $grossProfit = [];
        $grossProfitMap = [];
        foreach ($grossProfitData as $k => $v) {
            $grossProfit[$v["market_user_name"]] = ($grossProfit[$v["market_user_name"]]??0) + ($v["deposit"] + $v["final_payment"]);
            $grossProfitMap[$v["market_user_name"]] = ($grossProfitMap[$v["market_user_name"]]??0) + ($v["deposit"] + $v["final_payment"])*$v["commission_ratio"];
        }

        $checkFeeMap = [];
        $manuscriptFeeMap = [];
        # 检测费/稿费
        $fee = $mapper->marketFee(["om.id" => $mainOrderIds]);
        foreach ($fee as $k => $v) {
            $checkFeeMap[$v["market_user_name"]] = ($checkFeeMap[$v["market_user_name"]]??0) + $v["check_fee"];
            $manuscriptFeeMap[$v["market_user_name"]] = ($manuscriptFeeMap[$v["market_user_name"]]??0) + $v["manuscript_fee"];
        }

        $total = array_sum(array_values($depositData)) + array_sum(array_values($finalPaymentData));
        $retData = [];
        foreach ($tmp as $k => $v) {
            $totalAmount = ($depositData[$k]??0) + ($finalPaymentData[$k]??0);
            $grossProfit_ = $grossProfit[$k] - $checkFeeMap[$k] - $manuscriptFeeMap[$k] - $grossProfitMap[$k];
            $item = [
                "name" => $k,
                "market_user_id" => $v[0]["market_user_id"],
                "total_amount" => $totalAmount,
                "refund_amount" => $refundData[$k]??0,
                "total_count" => $countMap[$k]??0,
                "gross_profit" => floatval(round($grossProfit_, 2)),
                "gross_profit_rate" => ($grossProfit_==0||$grossProfit[$k]==0)?"0%":round(($grossProfit_ / $grossProfit[$k])*100, 2)."%",
                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%"
            ];
            $retData[] = $item;
        }
        $deposit = array_combine(array_column($_depositData, "market_user_name"), array_column($_depositData, "market_user"));
        $final = array_combine(array_column($_finalPayment, "market_user_name"), array_column($_finalPayment, "market_user"));
        $refund = array_combine(array_column($_refund, "market_user_name"), array_column($_refund, "market_user"));
        $tail = array_merge($final, $refund, $deposit);
        foreach ($tail as $k => $v) {
            if (!in_array($k, array_keys($tmp)) && !is_null($v)) {
                $grossProfit_ = $grossProfit[$k] - $checkFeeMap[$k] - $manuscriptFeeMap[$k] - $grossProfitMap[$k];
                $item = [
                    "name" => $k,
                    "market_user_id" => $v,
                    "total_amount" => $finalPaymentData[$k]??0,
                    "refund_amount" => $refundData[$k]??0,
                    "total_count" => 0,
                    "gross_profit" => floatval(round($grossProfit_, 2)),
                    "gross_profit_rate" => ($grossProfit_==0||$grossProfit[$k]==0)?"0%":round(($grossProfit_ / $grossProfit[$k])*100, 2)."%",
                    "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%"
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
     * 市场人员详情来源BI数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function marketUserOriginBi($param) {
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
        $where[] = ["o.market_user", "=", $param["market_user_id"]];
        # 获取数据
        $mapper = new OrdersMainMapper();
        $marketDetailData = $mapper->marketDetailData($where);
        $tmp = [];
        $countMap = [];
        foreach ($marketDetailData as $k => $v) {
            $tmp[$v["origin_name"]][] = $v;
            $countMap[$v["origin_name"]] = ($countMap[$v["origin_name"]]??0) + 1;
        }

        # 定金
        $map = [
            ["od.create_time", ">=", $where[0][2]],
            ["od.create_time", "<=", $where[1][2]],
            ["o.market_user", "=", $param["market_user_id"]],
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
            ["o.market_user", "=", $param["market_user_id"]],
        ];
        $_refund = $mapper->marketDetailRefund($map);
        $refundData = processAmount($_refund, "refund_amount", "origin_name");
        $marketDetailAmountData = array_merge($_depositData, $_finalPayment, $_refund);

        # 毛利润
        $mainOrderIds = array_unique(array_merge(array_column($marketDetailData, "main_order_id"), array_column($marketDetailAmountData, "main_order_id")));
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
            $totalAmount = ($depositData[$k]??0) + ($finalPaymentData[$k]??0);
            $grossProfit_ = $grossProfit[$k] - $checkFeeMap[$k] - $manuscriptFeeMap[$k] - $grossProfitMap[$k];

            $item = [
                "origin_name" => $k,
                "total_amount" => $totalAmount,
                "total_count" => $countMap[$k]??0,
                "refund_amount" => $refundData[$k]??0,
                "gross_profit" => floatval(round($grossProfit_, 2)),
                "gross_profit_rate" => ($grossProfit_==0||$grossProfit[$k]==0)?"0%":round(($grossProfit_ / $grossProfit[$k])*100, 2)."%",
                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%"
            ];
            $retData[] = $item;
        }
        $tail = array_merge(array_keys($depositData), array_keys($finalPaymentData), array_keys($refundData));
        foreach ($tail as $k => $v) {
            if (!in_array($v, array_keys($tmp))) {
                $grossProfit_ = $grossProfit[$v] - $checkFeeMap[$v] - $manuscriptFeeMap[$v] - $grossProfitMap[$v];
                $item = [
                    "origin_name" => $v,
                    "total_amount" => $finalPaymentData[$v]??0,
                    "total_count" => 0,
                    "refund_amount" => $refundData[$v]??0,
                    "gross_profit" => floatval(round($grossProfit_, 2)),
                    "gross_profit_rate" => ($grossProfit_==0||$grossProfit[$v]==0)?"0%":round(($grossProfit_ / $grossProfit[$v])*100, 2)."%",
                    "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%"
                ];
                $retData[] = $item;
            }
        }
        $sort = array_column($retData, "total_amount");
        array_multisort($sort, SORT_DESC, $retData);
        $max = $retData[0]["total_amount"]??0;
        foreach ($retData as $k => $v) {
            $retData[$k]["rank"] = $k+1;
            $retData[$k]["champion_ratio"] = $max==0?'0%':round(($v["total_amount"] / $max) * 100, 2)."%";
        }
        return $retData;
    }
}
