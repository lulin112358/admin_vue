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
                ["om.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        # 获取市场专员订单数量数据
        $marketUserData = (new OrdersMainMapper())->marketUserBiData($where);
        # 获取市场专员订单金额数据
        $map = function ($query) use ($where) {
            $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                ->whereOr(function ($query) use ($where) {
                    $query->where([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
                })
                ->whereOr(function ($query) use ($where) {
                    $query->where([["refund_time", ">=", $where[0][2]], ["refund_time", "<=", $where[1][2]]]);
                });
        };
        $marketUserAmountData = (new OrdersMainMapper())->amountBiData($map);
        $total = array_sum(array_column($marketUserAmountData, "deposit")) + array_sum(array_column($marketUserAmountData, "final_payment"));
        $tmp = [];
        $orderData = [];
        foreach ($marketUserAmountData as $k => $v) {
            $tmp[$v["market_user"]][] = $v;
            $orderData[$v["main_order_id"]] = $v["deposit"] + $v["final_payment"];
        }
        $amountData = [];
        foreach ($tmp as $k => $v) {
            $amountData[$k]["deposit"] = array_sum(array_column($v, "deposit"));
            $amountData[$k]["final_payment"] = array_sum(array_column($v, "final_payment"));
            $amountData[$k]["total_amount"] = $amountData[$k]["deposit"] + $amountData[$k]["final_payment"];
            $amountData[$k]["refund_amount"] = array_sum(array_column($v, "refund_amount"));
        }
        $tmp = [];
        foreach ($marketUserData as $k => $v)
            $tmp[$v["market_user"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $commission = 0;
            foreach ($v as $idx => $val) {
                $commission += ($val["commission_ratio"] < 1 ? (round($orderData[$val["id"]] * $val["commission_ratio"], 2)) : $val["commission_ratio"]);
            }
            $totalAmount = $amountData[$v[0]["market_user_id"]]["total_amount"];
            $checkFee = array_sum(array_column($v, "check_fee"));
            $manuscriptFee = array_sum(array_column($v, "manuscript_fee"));
            $grossProfit = $totalAmount - $checkFee - $manuscriptFee - $commission;
            $item = [
                "name" => $k,
                "market_user_id" => $v[0]["market_user_id"],
                "total_amount" => $totalAmount,
                "refund_amount" => $amountData[$v[0]["market_user_id"]]["refund_amount"],
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
                ["om.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        $where[] = ["o.market_user", "=", $param["market_user_id"]];
        # 获取数据
        $marketUserOriginData = (new OrdersMainMapper())->marketUserOriginBiData($where);
        $map = function ($query) use ($where) {
            $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                ->whereOr(function ($query) use ($where) {
                    $query->where([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
                })
                ->whereOr(function ($query) use ($where) {
                    $query->where([["refund_time", ">=", $where[0][2]], ["refund_time", "<=", $where[1][2]]]);
                });
        };
        $marketUserOriginAmountData = (new OrdersMainMapper())->amountBiData($map);
        $total = array_sum(array_column($marketUserOriginAmountData, "deposit")) + array_sum(array_column($marketUserOriginAmountData, "final_payment"));
        $tmp = [];
        $orderData = [];
        foreach ($marketUserOriginAmountData as $k => $v) {
            $tmp[$v["origin_id"]][] = $v;
            $orderData[$v["main_order_id"]] = $v["deposit"] + $v["final_payment"];
        }
        $amountData = [];
        foreach ($tmp as $k => $v) {
            $amountData[$k]["total_amount"] = array_sum(array_column($v, "deposit")) + array_sum(array_column($v, "final_payment"));
            $amountData[$k]["refund_amount"] = array_sum(array_column($v, "refund_amount"));
        }
        $tmp = [];
        foreach ($marketUserOriginData as $k => $v)
            $tmp[$v["origin_name"]][] = $v;

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
