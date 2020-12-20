<?php


namespace app\admin\service;


use app\mapper\OrdersMainMapper;

class OriginBiService
{
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
                ["om.create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["om.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        # 获取数据
        $originBiData = (new OrdersMainMapper())->originBiData($where);
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
        $originBiAmountData = (new OrdersMainMapper())->amountBiData($map);
        $total = array_sum(array_column($originBiAmountData, "deposit")) + array_sum(array_column($originBiAmountData, "final_payment"));
        $tmp = [];
        $orderData = [];
        foreach ($originBiAmountData as $k => $v) {
            $tmp[$v["origin_id"]][] = $v;
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
        foreach ($originBiData as $k => $v)
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
                "origin_id" => $v[0]["origin_id"],
                "total_amount" => $totalAmount,
                "total_count" => count($v),
                "refund_amount" => $amountData[$v[0]["origin_id"]]["refund_amount"],
                "gross_profit" => floatval(round($grossProfit, 2)),
                "gross_profit_rate" => $totalAmount==0?"0%":round(($grossProfit / $totalAmount)*100, 2)."%",
                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%",
                "supplier_commission" => $commission,
                "deposit" => $amountData[$v[0]["origin_id"]]["deposit"],
                "final_payment" => $amountData[$v[0]["origin_id"]]["final_payment"],
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


    public function originDetailBi($param) {
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["create_time", ">=", strtotime($param["range_time"][0])],
                ["create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["create_time", "<=", time()],
            ];
        }
        $where[] = ["origin_id", "=", $param["origin_id"]];
        # 获取数据
        $originDetailBiData = (new OrdersMainMapper())->originDetailBiData($where);
        $tmp = [];
        foreach ($originDetailBiData as $k => $v)
            $tmp[date("Ymd", $v["create_time"])][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $commission = 0;
            $totalAmount = array_sum(array_column($v, "total_amount"));
            if ($v[0]["commission_ratio"] < 1) {
                $commission += (round($totalAmount * $v[0]["commission_ratio"], 2));
            }else {
                $commission += array_sum(array_column($v, "commission_ratio"));
            }

            $checkFee = array_sum(array_column($v, "check_fee"));
            $manuscriptFee = array_sum(array_column($v, "manuscript_fee"));
            $grossProfit = $totalAmount - $checkFee - $manuscriptFee - $commission;
            $item = [
                "origin_name" => $k,
                "origin_id" => $v[0]["origin_id"],
                "total_amount" => $totalAmount,
                "total_count" => count($v),
                "refund_amount" => array_sum(array_column($v, "refund_amount")),
                "gross_profit" => $grossProfit,
                "gross_profit_rate" => $totalAmount==0?"0%":round(($grossProfit / $totalAmount)*100, 2)."%",
//                "deal_rate" => $total==0?"0%":round(($totalAmount / $total)*100, 2)."%",
                "supplier_commission" => $commission,
                "deposit" => array_sum(array_column($v, "deposit")),
                "final_payment" => array_sum(array_column($v, "final_payment")),
            ];
            $retData[] = $item;
        }
    }
}
