<?php


namespace app\admin\service;


use app\mapper\AccountMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\UserMapper;
use Carbon\Carbon;
use excel\Excel;

class CustomerBiService
{
    private function process($data, $type) {
        $data_ = [];
        foreach ($data as $k => $v) {
            $data_[$v["name"]][] = $v;
        }
        $_data = [];
        foreach ($data_ as $k => $v) {
            foreach ($v as $key => $val) {
                $_data[$k][$val["account_id"]][] = $val;
            }
        }
        $ret = [];
        foreach ($_data as $k => $v) {
            foreach ($v as $key => $val) {
                $ret[$k][$key] = array_sum(array_column($val, $type));
            }
        }
        return $ret;
    }

    /**
     * 获取
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerBiCount($param) {
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
        $type = $param["type"]??1;
        $sortCols = $this->accountColSort($where, $type);
        $fields = array_column($sortCols, "field");
        # 查找客服业绩
        if ($type == 1) {
            $customerData = (new OrdersMainMapper())->customerData($where);
        }else{
            $map = function ($query) use ($where) {
                $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                    ->whereOr(function ($query) use ($where) {
                        $query->where([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
                    });
            };
            $customerData = (new OrdersMainMapper())->customerAmountData($map);
            # 定金
            $_depositData = collect($customerData)->where("deposit_time", ">=", $where[0][2])
                ->where("deposit_time", "<=", $where[1][2])->toArray();
            $deposit = $this->process($_depositData, "deposit");

            # 尾款
            $_finalPayment = collect($customerData)->where("final_payment_time", ">=", $where[0][2])
                ->where("final_payment_time", "<=", $where[1][2])->toArray();
            $finalPayment = $this->process($_finalPayment, "final_payment");
        }

        $customerTmp = [];
        foreach ($customerData as $k => $v)
            $customerTmp[$v["name"]][] = $v;

        $accountTem = [];
        foreach ($customerTmp as $k => $v) {
            foreach ($v as $idx => $val) {
                $accountTem[$k][$val["account_id"]][] = $val;
            }
        }

        $retData = [];
        if ($type == 1) {
            foreach ($accountTem as $k => $v) {
                $item["customer_name"] = $k;
                $total = 0;
                foreach ($v as $idx => $val) {
                    if (!empty($idx) && in_array("account_".$idx, $fields)) {
                        $item["account_".$idx] = count($val);
                        $total += count($val);
                    }
                }
                $item["total"] = $total;
                $retData[] = $item;
                $item = [];
            }
            $sort = array_column($retData, "total");
        }else {
            foreach ($accountTem as $k => $v) {
                $item["customer_name"] = $k;
                foreach ($v as $idx => $val) {
                    if (!empty($idx) && in_array("account_".$idx, $fields)) {
                        $item["account_".$idx] = ($deposit[$k][$idx]??0) + ($finalPayment[$k][$idx]??0);
                    }
                }
                $item["total_amount"] = (array_sum(array_values($deposit[$k]??[]))) + (array_sum(array_values($finalPayment[$k]??[])));
                $retData[] = $item;
                $item = [];
            }
            $sort = array_column($retData, "total_amount");
        }
        array_multisort($sort, SORT_DESC, $retData);
        return ["cols" => $sortCols, "data" => $retData];
    }


    /**
     * 获取所有排序好的账号列
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountColSort($where, $type = 1) {
        # 获取所有来源
        $accountData = (new AccountMapper())->selectBy(["status" => 1], "id, simple_name as title, concat('account_', id) as field");
        $accounts = array_column($accountData, "id");
        # 获取所有来源排序
        if ($type == 1) {
            $accountSort = (new OrdersMainMapper())->accountSortData($where);
        }else {
            $accountSort = (new OrdersMainMapper())->accountAmountSortData($where);
            # 定金
            $_depositData = collect($accountSort)->where("deposit_time", ">=", $where[0][2])
                ->where("deposit_time", "<=", $where[1][2])->toArray();
            $depositData = processAmount($_depositData, "deposit", "account_id");
            # 尾款
            $_finalPayment = collect($accountSort)->where("final_payment_time", ">=", $where[0][2])
                ->where("final_payment_time", "<=", $where[1][2])->toArray();
            $finalPaymentData = processAmount($_finalPayment, "final_payment", "account_id");
            $accountSort = [];
            foreach ($depositData as $k => $v) {
                $accountSort[$k]["total_amount"] = $v;
                $accountSort[$k]["account_id"] = $k;
            }
            foreach ($finalPaymentData as $k => $v) {
                $accountSort[$k]["total_amount"] = $accountSort[$k]["total_amount"]??0;
                $accountSort[$k]["total_amount"] += $v;
                $accountSort[$k]["account_id"] = $k;
            }
            $sort = array_column($accountSort, "total_amount");
            array_multisort($sort, SORT_DESC, $accountSort);
        }
        $accountSort = array_column($accountSort, "account_id");
        $_accountData = [];
        foreach ($accountData as $k => $v) {
            if (!in_array($v["id"], $accountSort)) {
                array_push($accountSort, $v["id"]);
            }
            $id = $v["id"];
            unset($v["id"]);
            $v["showHeaderOverflow"] = true;
            $v["showOverflow"] = true;
            $v["showFooterOverflow"] = true;
            $v["minWidth"] = '100px';
            $_accountData[$id] = $v;
        }
        foreach ($accountSort as $k => $v) {
            if (!in_array($v, $accounts))
                unset($accountSort[$k]);
        }
        $accountSort = array_values($accountSort);
        $accountData = array_values(array_replace(array_flip($accountSort), $_accountData));
        array_unshift($accountData, ["title" => "序号", "fixed" => "left", "showHeaderOverflow" => true, "showOverflow" => true, "showFooterOverflow" => true, "type" => "seq", "width" => 60]);
        if ($type == 1) {
            array_unshift($accountData, ["title" => "笔数", "field" => "total", "showOverflow" => true, "showHeaderOverflow" => true, "showFooterOverflow" => true, "minWidth" => "100px"]);
        }else{
            array_unshift($accountData, ["title" => "金额", "field" => "total_amount", "showOverflow" => true, "showHeaderOverflow" => true, "showFooterOverflow" => true, "minWidth" => "100px"]);
        }
        array_unshift($accountData, ["title" => "姓名", "field" => "customer_name", "showOverflow" => true, "showHeaderOverflow" => true, "showFooterOverflow" => true, "minWidth" => "100px"]);
        return $accountData;
    }

    /**
     * 客服接单数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerOrderBi($param) {
        // 设置中文
        Carbon::setLocale("zh");
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["om.create_time", ">=", strtotime($param["range_time"][0])],
                ["om.create_time", "<=", strtotime($param["range_time"][1])]
            ];
            $days = Carbon::parse($param["range_time"][0])->diffInDays(Carbon::parse($param["range_time"][1]));
        }else {
            $where = [
                ["om.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["om.create_time", "<=", time()],
            ];
            $days = (new Carbon())->diffInDays(Carbon::parse(date("Y-m-d", time())));
        }
        # 查找客服业绩单数
        $customerData = (new OrdersMainMapper())->customerOrderData($where);
        # 客服业绩金额
        $whereCloser = function ($query) use ($where) {
            $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                ->whereOr(function ($query) use ($where) {
                    $query->where([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
                });
        };
        $cusAmountData = (new OrdersMainMapper())->amountBiData($whereCloser);
        $amountTmp = [];
        foreach ($cusAmountData as $k => $v) {
            $amountTmp[$v["name"]][] = $v;
        }
        # 定金
        $_depositData = collect($cusAmountData)->where("deposit_time", ">=", $where[0][2])
            ->where("deposit_time", "<=", $where[1][2])->toArray();
        $depositData = processAmount($_depositData, "deposit");
        # 尾款
        $_finalPayment = collect($cusAmountData)->where("final_payment_time", ">=", $where[0][2])
            ->where("final_payment_time", "<=", $where[1][2])->toArray();
        $finalPaymentData = processAmount($_finalPayment, "final_payment");

        # 客服入职日期
        $userData = (new UserMapper())->userData();
        foreach ($userData as $k => $v) {
            if (!isset($v["extend"]["entry_time"])) {
                $userData[$k]["entry_time"] = $userData[$k]["create_time"];
            }else {
                if ($v["extend"]["entry_time"]==0) {
                    $userData[$k]["entry_time"] = $userData[$k]["create_time"];
                }else {
                    $userData[$k]["entry_time"] = date("Y-m-d H:i:s", $v["extend"]["entry_time"]);
                }
            }
        }
        $entryTime = array_combine(array_column($userData, "id"), array_column($userData, "entry_time"));

        $customerTmp = [];
        foreach ($customerData as $k => $v)
            $customerTmp[$v["name"]][] = $v;

        $retData = [];
        foreach ($customerTmp as $k => $v) {
            $total = ($depositData[$k]??0) + ($finalPaymentData[$k]??0);
            $item = [
                "customer_name" => $k,
                "total_count" => count($v),
                "total_amount" => $total,
                "day_count" => round(count($v) / $days, 1),
                "day_amount" => floatval(round($total / $days, 2)),
                "entry_days" => (new Carbon())->diffInDays(Carbon::parse($entryTime[$v[0]["customer_id"]]))
            ];
            $retData[] = $item;
        }

        foreach ($amountTmp as $k => $v) {
            if (!in_array($k, array_keys($customerTmp))) {
                $total = ($depositData[$k]??0) + ($finalPaymentData[$k]??0);
                $item = [
                    "customer_name" => $k,
                    "total_count" => 0,
                    "total_amount" => $total,
                    "day_count" => 0,
                    "day_amount" => floatval(round($total / $days, 2)),
                    "entry_days" => (new Carbon())->diffInDays(Carbon::parse($entryTime[$v[0]["customer_id"]]))
                ];
                $retData[] = $item;
            }
        }
        $sort = array_column($retData, "total_count");
        array_multisort($sort, SORT_DESC, $retData);
        return $retData;
    }

    /**
     * 客服订单业绩BI数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cusOrderPerfBi($param) {
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
        # 查找客服业绩数量
        $customerData = (new OrdersMainMapper())->customerOrderData($where);
        # 获取客服业绩金额
        $map = function ($query) use ($where) {
            $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                ->whereOr(function ($query) use ($where) {
                    $query->where([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
                })
                ->whereOr(function ($query) use ($where) {
                    $query->where([["refund_time", ">=", $where[0][2]], ["refund_time", "<=", $where[1][2]]]);
                });
        };
        $cusAmountData = (new OrdersMainMapper())->amountBiData($map);
        $amountTmp = [];
        $customerMap = [];
        foreach ($cusAmountData as $k => $v) {
            if (!in_array($v["name"], $amountTmp)) {
                $amountTmp[] = $v["name"];
                $customerMap[$v["name"]] = $v["customer_id"];
            }
        }

        # 定金
        $_depositData = collect($cusAmountData)->where("deposit_time", ">=", $where[0][2])
            ->where("deposit_time", "<=", $where[1][2])->toArray();
        $depositData = processAmount($_depositData, "deposit");

        # 尾款
        $_finalPayment = collect($cusAmountData)->where("final_payment_time", ">=", $where[0][2])
            ->where("final_payment_time", "<=", $where[1][2])->toArray();
        $finalPaymentData = processAmount($_finalPayment, "final_payment");

        # 退款
        $_refund = collect($cusAmountData)->where("refund_time", ">=", $where[0][2])
            ->where("refund_time", "<=", $where[1][2])->toArray();
        $refundData = processAmount($_refund, "refund_amount");
        # 总入账
        $totalAmountAll = array_sum(array_values($depositData)) + array_sum(array_values($finalPaymentData));

        $tmp = [];
        foreach ($customerData as $k => $v)
            $tmp[$v["name"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $cusTotalAmount = ($depositData[$k]??0) + ($finalPaymentData[$k]??0);
            $item = [
                "name" => $k,
                "deal_rate" => (round($cusTotalAmount / $totalAmountAll, 2) * 100) . "%",
                "total_amount" => $cusTotalAmount,
                "total_count" => count($v),
                "write_count" => collect($v)->where("category_id", "in", [9])->count(),
                "reduce_repeat_count" => collect($v)->where("category_id", "in", [7,8,10])->count(),
                "other_count" => collect($v)->whereNotIn("category_id", [7,8,9,10])->count(),
                "deposit" => $depositData[$k]??0,
                "final_payment" => $finalPaymentData[$k]??0,
                "refund_amount" => $refundData[$k]??0,
                "customer_id" => $v[0]["customer_id"],
            ];
            $retData[] = $item;
        }

        foreach ($amountTmp as $k => $v) {
            if (!in_array($v, array_keys($tmp))) {
                $cusTotalAmount = ($depositData[$v]??0) + ($finalPaymentData[$v]??0);
                $item = [
                    "name" => $v,
                    "deal_rate" => (round($cusTotalAmount / $totalAmountAll, 2) * 100) . "%",
                    "total_amount" => $cusTotalAmount,
                    "total_count" => 0,
                    "write_count" => 0,
                    "reduce_repeat_count" => 0,
                    "other_count" => 0,
                    "deposit" => $depositData[$v]??0,
                    "final_payment" => $finalPaymentData[$v]??0,
                    "refund_amount" => $refundData[$v]??0,
                    "customer_id" => $customerMap[$v],
                ];
                $retData[] = $item;
            }
        }

        $sort = array_column($retData, "total_amount");
        array_multisort($sort, SORT_DESC, $retData);
        foreach ($retData as $k => $v) {
            $retData[$k]["rank"] = $k+1;
        }
        return $retData;
    }


    /**
     * 客服订单业绩详情BI数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cusOrderPerfDetailBi($param) {
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $startTime = strtotime($param["range_time"][0]);
            $endTime = strtotime($param["range_time"][1]);
        }else {
            $startTime = strtotime(date("Y-m-d", time()));
            $endTime = time();
        }
        # 获取客服业绩金额
        $map = function ($query) use ($startTime, $endTime, $param) {
            $query->where([["bav.deposit_time", ">=", $startTime], ["bav.deposit_time", "<=", $endTime], ["bav.customer_id", "=", $param["customer_id"]]])
                ->whereOr(function ($query) use ($startTime, $endTime, $param) {
                    $query->where([["bav.final_payment_time", ">=", $startTime], ["bav.final_payment_time", "<=",$endTime], ["bav.customer_id", "=", $param["customer_id"]]]);
                })
                ->whereOr(function ($query) use ($startTime, $endTime, $param) {
                    $query->where([["bav.refund_time", ">=", $startTime], ["bav.refund_time", "<=", $endTime], ["bav.customer_id", "=", $param["customer_id"]]]);
                });
        };
        $cusAmountData = (new OrdersMainMapper())->amountBiDataWithOrderSn($map);

        # 定金
        $_depositData = collect($cusAmountData)->where("deposit_time", ">=", $startTime)
            ->where("deposit_time", "<=", $endTime)->toArray();

        # 尾款
        $_finalPayment = collect($cusAmountData)->where("final_payment_time", ">=", $startTime)
            ->where("final_payment_time", "<=", $endTime)->toArray();

        # 退款
        $_refund = collect($cusAmountData)->where("refund_time", ">=", $startTime)
            ->where("refund_time", "<=", $endTime)->toArray();

        $data = ["deposit" => $_depositData, "final_payment" => $_finalPayment, "refund" => $_refund];
        $retData = [];
        foreach ($data as $k => $v) {
            foreach ($v as $key => $val) {
                $item = [
                    "origin_name" => $val["origin_name"],
                    "deposit" => floatval($k=='deposit'?$val["deposit"]:0),
                    "final_payment" => floatval($k=='final_payment'?$val["final_payment"]:0),
                    "refund_amount" => floatval($k=='refund'?$val["refund_amount"]:0),
                    "amount_time" => date("Y-m-d H", $val[$k.'_time']),
                    "order_sn" => $val["order_sn"]
                ];
                $retData[] = $item;
            }

        }
        return $retData;
    }


    public function cusOrderPerExport($param) {
        $data = $this->cusOrderPerfBi($param);
        $header = [
            ["姓名", "name"],
            ["排名", "rank"],
            ["成交占比", "deal_rate"],
            ["入账", "total_amount"],
            ["成交单数", "total_count"],
            ["写作", "write_count"],
            ["降重", "reduce_repeat_count"],
            ["其他", "other_count"],
            ["定金", "deposit"],
            ["尾款", "final_payment"],
            ["退款", "refund_amount"],
        ];
        return Excel::exportData($data, $header, "客服订单业绩数据");
    }
}
