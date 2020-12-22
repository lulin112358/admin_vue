<?php


namespace app\admin\service;


use app\mapper\OrdersMainMapper;
use excel\Excel;

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
                ["om.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        # 获取数据
        $originBiData = (new OrdersMainMapper())->originBiData($where);
        $_amountData = [];
        $_orderData = [];
        foreach ($originBiData as $k => $v) {
            $_amountData[$v["origin_id"]][] = $v;
            $_orderData[$v['id']][] = $v;
        }
        $orderData = [];
        foreach ($_orderData as $k => $v) {
            $orderData[$k] = array_sum(array_column($v, "deposit")) + array_sum(array_column($v, "final_payment"));
        }
        # 获取订单金额数据
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
        # 定金
        $_depositData = collect($originBiAmountData)->where("deposit_time", ">=", $where[0][2])
            ->where("deposit_time", "<=", $where[1][2])->toArray();
        $depositData = processAmount($_depositData, "deposit", "origin_name");

        # 尾款
        $_finalPayment = collect($originBiAmountData)->where("final_payment_time", ">=", $where[0][2])
            ->where("final_payment_time", "<=", $where[1][2])->toArray();
        $finalPaymentData = processAmount($_finalPayment, "final_payment", "origin_name");

        # 退款
        $_refund = collect($originBiAmountData)->where("refund_time", ">=", $where[0][2])
            ->where("refund_time", "<=", $where[1][2])->toArray();
        $refundData = processAmount($_refund, "refund_amount", "origin_name");
        $total = array_sum(array_values($depositData)) + array_sum(array_values($finalPaymentData));
        $amountData = [];
        foreach ($depositData as $k => $v) {
            $amountData[$k] = $v;
        }
        foreach ($finalPaymentData as $k => $v) {
            $amountData[$k] = $amountData[$k]??0;
            $amountData[$k] += $v;
        }

        $tmp = [];
        foreach ($originBiData as $k => $v)
            $tmp[$v["origin_name"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $totalAmount = $amountData[$k];
            $commission = 0;
            foreach ($v as $key => $val) {
                $commission += ($val["commission_ratio"] < 1 ? (round($orderData[$val["id"]] * $val["commission_ratio"], 2)) : $val["commission_ratio"]);
            }

            $checkFee = array_sum(array_column($v, "check_fee"));
            $manuscriptFee = array_sum(array_column($v, "manuscript_fee"));
            $grossProfit = $totalAmount - $checkFee - $manuscriptFee - $commission;
            $deposit = $depositData[$k]??0;
            $finalPayment = $finalPaymentData[$k]??0;
            $item = [
                "origin_name" => $k,
                "origin_id" => $v[0]["origin_id"],
                "total_amount" => $totalAmount,
                "total_count" => count($v),
                "refund_amount" => $refundData[$k]??0,
                "gross_profit" => floatval(round($grossProfit, 2)),
                "gross_profit_rate" => $totalAmount==0?"0%":round(($grossProfit / $totalAmount)*100, 2)."%",
                "deal_rate" => $total==0?"0%":round((($deposit+$finalPayment) / $total)*100, 2)."%",
                "supplier_commission" => floatval(round($commission, 2)),
                "deposit" => $deposit,
                "final_payment" => $finalPayment,
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
            $startTime = strtotime($param["range_time"][0]);
            $endTime = strtotime($param["range_time"][1]);
        }else {
            $startTime = strtotime(date("Y-m-d", time()));
            $endTime = time();
        }
        # 获取数据
        $map = function ($query) use ($param, $startTime, $endTime) {
            $query->where([["deposit_time", ">=", $startTime], ["deposit_time", "<=", $endTime], ["origin_id", "=", $param["origin_id"]]])
                ->whereOr(function ($query) use ($param, $startTime, $endTime) {
                    $query->where([["final_payment_time", ">=", $startTime], ["final_payment_time", "<=", $endTime], ["origin_id", "=", $param["origin_id"]]]);
                })
                ->whereOr(function ($query) use ($param, $startTime, $endTime) {
                    $query->where([["refund_time", ">=", $startTime], ["refund_time", "<=", $endTime], ["origin_id", "=", $param["origin_id"]]]);
                });
        };

        $originDetailBiData = (new OrdersMainMapper())->amountBiData($map);
        $deposit = [];
        $finalPayment = [];
        $refund = [];
        foreach ($originDetailBiData as $k => $v) {
            if (!is_null($v["deposit_time"])) {
                if ($v["deposit_time"] >= $startTime && $v["deposit_time"] <= $endTime) {
                    $item = [
                        "deposit" => $v["deposit"],
                        "amount_time" => date("Y-m-d H点", $v["deposit_time"]),
                        "name" => $v["name"],
                        "order_sn" => $v["order_sn"]
                    ];
                    $deposit[] = $item;
                }
            }
            if (!is_null($v["final_payment_time"])) {
                if ($v["final_payment_time"] >= $startTime && $v["final_payment_time"] <= $endTime) {
                    $item = [
                        "final_payment" => $v["final_payment"],
                        "amount_time" => date("Y-m-d H点", $v["final_payment_time"]),
                        "name" => $v["name"],
                        "order_sn" => $v["order_sn"]
                    ];
                    $finalPayment[] = $item;
                }
            }
            if (!is_null($v['refund_time'])) {
                if ($v["refund_time"] >= $startTime && $v["refund_time"] <= $endTime) {
                    $item = [
                        "refund_amount" => $v["refund_amount"],
                        "amount_time" => date("Y-m-d H点", $v["refund_time"]),
                        "name" => $v["name"],
                        "order_sn" => $v["order_sn"]
                    ];
                    $refund[] = $item;
                }
            }
        }

        $data = array_merge($deposit, $finalPayment, $refund);

        $retData = [];
        foreach ($data as $k => $v) {
            $item = [
                "name" => $v["name"],
                "deposit" => isset($v["deposit"])?floatval($v["deposit"]):0,
                "final_payment" => isset($v["final_payment"])?floatval($v["final_payment"]):0,
                "refund_amount" => isset($v["refund_amount"])?floatval($v["refund_amount"]):0,
                "amount_time" => $v["amount_time"],
                "order_sn" => $v["order_sn"]
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
}
