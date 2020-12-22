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
                ["om.create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["om.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        # 获取市场专员订单数量数据
        $marketUserData = (new OrdersMainMapper())->marketUserBiData($where);
        $total = array_sum(array_column($marketUserData, "deposit")) + array_sum(array_column($marketUserData, "final_payment"));

        $tmp = [];
        $_orderData = [];
        foreach ($marketUserData as $k => $v) {
            $tmp[$v["market_user"]][] = $v;
            $_orderData[$v["id"]][] = $v;
        }
        $orderData = [];
        foreach ($_orderData as $k => $v) {
            $orderData[$k] = array_sum(array_column($v, "deposit")) + array_sum(array_column($v, "final_payment"));
        }

        $retData = [];
        foreach ($tmp as $k => $v) {
            $commission = 0;
            foreach ($v as $idx => $val) {
                $commission += ($val["commission_ratio"] < 1 ? (round($orderData[$val["id"]] * $val["commission_ratio"], 2)) : $val["commission_ratio"]);
            }
            $totalAmount = array_sum(array_column($v, "deposit")) + array_sum(array_column($v, "final_payment"));
            $checkFee = array_sum(array_column($v, "check_fee"));
            $manuscriptFee = array_sum(array_column($v, "manuscript_fee"));
            $grossProfit = $totalAmount - $checkFee - $manuscriptFee - $commission;
            $item = [
                "name" => $k,
                "market_user_id" => $v[0]["market_user_id"],
                "total_amount" => $totalAmount,
                "refund_amount" => array_sum(array_column($v, "refund_amount")),
                "total_count" => count($v),
                "gross_profit" => floatval(round($grossProfit, 2)),
                "gross_profit_rate" => $totalAmount==0?"0%":round(($grossProfit / $totalAmount)*100, 2)."%",
                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%"
            ];
            $retData[] = $item;
        }
        $sort = array_column($retData, "total_amount");
        array_multisort($sort, SORT_DESC, $retData);
        foreach ($retData as $k => $v) {
            $retData[$k]["rank"] = $k+1;
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
                ["om.create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["om.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        $where[] = ["o.market_user", "=", $param["market_user_id"]];
        # 获取数据
        $marketUserOriginData = (new OrdersMainMapper())->marketUserOriginBiData($where);
        $total = array_sum(array_column($marketUserOriginData, "deposit")) + array_sum(array_column($marketUserOriginData, "final_payment"));
        $tmp = [];
        $_amountData = [];
        $_orderData = [];
        foreach ($marketUserOriginData as $k => $v) {
            $tmp[$v["origin_name"]][] = $v;
            $_amountData[$v["origin_id"]][] = $v;
            $_orderData[$v["id"]][] = $v;
        }
        $amountData = [];
        foreach ($_amountData as $k => $v) {
            $amountData[$k]["total_amount"] = array_sum(array_column($v, "deposit")) + array_sum(array_column($v, "final_payment"));
            $amountData[$k]["refund_amount"] = array_sum(array_column($v, "refund_amount"));
        }
        $orderData = [];
        foreach ($_orderData as $k => $v) {
            $orderData[$k] = array_sum(array_column($v, "deposit")) + array_sum(array_column($v, "final_payment"));
        }

        $retData = [];
        foreach ($tmp as $k => $v) {
            $totalAmount = $amountData[$v[0]["origin_id"]]["total_amount"];
            $commission = 0;
            foreach ($v as $key => $val) {
                $commission += ($val["commission_ratio"] < 1 ? (round($orderData[$val["id"]] * $val["commission_ratio"], 2)) : $val["commission_ratio"]);
            }
            $checkFee = array_sum(array_column($v, "check_fee"));
            $manuscriptFee = array_sum(array_column($v, "manuscript_fee"));
            $grossProfit = $totalAmount - $checkFee - $manuscriptFee - $commission;
            $item = [
                "origin_name" => $k,
                "total_amount" => $totalAmount,
                "total_count" => count($v),
                "refund_amount" => $amountData[$v[0]["origin_id"]]["refund_amount"],
                "gross_profit" => floatval(round($grossProfit, 2)),
                "gross_profit_rate" => $totalAmount==0?"0%":round(($grossProfit / $totalAmount)*100, 2)."%",
                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%"
            ];
            $retData[] = $item;
        }
        $sort = array_column($retData, "total_amount");
        array_multisort($sort, SORT_DESC, $retData);
        foreach ($retData as $k => $v) {
            $retData[$k]["rank"] = $k+1;
        }
        return $retData;
    }
}
