<?php


namespace app\admin\service;


use app\mapper\AccountMapper;
use app\mapper\AttendanceMapper;
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
        $mapper = new OrdersMainMapper();
        if ($type == 1) {
            $customerData = $mapper->customerData($where);
        }else{
            $map = [["od.create_time", ">=", $where[0][2]], ["od.create_time", "<=", $where[1][2]]];
            # 定金
            $_depositData = $mapper->customerDepositData($map);
            $deposit = $this->process($_depositData, "deposit");

            # 尾款
            $_finalPayment = $mapper->customerFinalData($map);
            $finalPayment = $this->process($_finalPayment, "final_payment");

            $customerData = array_merge($_depositData, $_finalPayment);
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
        $mapper = new OrdersMainMapper();
        if ($type == 1) {
            $accountSort = $mapper->accountSortData($where);
        }else {
            $map = [["od.create_time", ">=", $where[0][2]], ["od.create_time", "<=", $where[1][2]]];
            # 定金
            $_depositData = $mapper->accountSortDepositData($map);
            $depositData = processAmount($_depositData, "deposit", "account_id");
            # 尾款
            $_finalPayment = $mapper->accountSortFinalData($map);
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
     * 客服订单业绩BI数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cusOrderPerfBi($param) {
        Carbon::setLocale("zh");
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["om.create_time", ">=", strtotime($param["range_time"][0])],
                ["om.create_time", "<=", strtotime($param["range_time"][1])]
            ];
            $attendanceMap = [
                ["a.create_time", ">=", strtotime($param["range_time"][0])],
                ["a.create_time", "<=", strtotime($param["range_time"][1])]
            ];
//            $days = Carbon::parse($param["range_time"][0])->diffInDays(Carbon::parse($param["range_time"][1]));
        }else {
            $where = [
                ["om.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["om.create_time", "<=", time()],
            ];
            $attendanceMap = [
                ["a.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["a.create_time", "<=", time()],
            ];
//            $days = (new Carbon())->diffInDays(Carbon::parse(date("Y-m-d", time())));
        }
        # 查找客服业绩数量
        $customerData = (new OrdersMainMapper())->customerOrderData($where);
        # 获取客服业绩金额
        $mapper = new OrdersMainMapper();
        $map = [["od.create_time", ">=", $where[0][2]], ["od.create_time", "<=", $where[1][2]]];
        # 定金
        $_depositData = $mapper->customerOrderDeposit($map);
        $depositData = processAmount($_depositData, "deposit");

        # 尾款
        $_finalPayment = $mapper->customerOrderFinal($map);
        $finalPaymentData = processAmount($_finalPayment, "final_payment");

        # 退款
        $map = [["od.refund_time", ">=", $where[0][2]], ["od.refund_time", "<=", $where[1][2]]];
        $_refund = $mapper->customerRefund($map);
        $refundData = processAmount($_refund, "refund_amount");

        # 客服在所选时间内的上工天数
        $data = (new AttendanceMapper())->attendances($attendanceMap);
        $tmp = [];
        foreach ($data as $k => $v)
            $tmp[$v["name"]][] = $v;

        $entryTime = [];
        foreach ($tmp as $k => $v) {
            $dataCollect = collect($v);
            # 出勤考勤
            $attendanceCount = $dataCollect->whereIn("type", [1, 2, 3, 6])->count();
            $attendanceCount = floatval($dataCollect->where("type", "=", 7)->count() / 2 + $attendanceCount);
            $entryTime[$v[0]["user_id"]] = $attendanceCount;
        }

        # 总入账
        $totalAmountAll = array_sum(array_values($depositData)) + array_sum(array_values($finalPaymentData));

        $tmp = [];
        foreach ($customerData as $k => $v)
            $tmp[$v["name"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $cusTotalAmount = floatval(round(($depositData[$k]??0) + ($finalPaymentData[$k]??0), 2));
            $entryDays = $entryTime[$v[0]["customer_id"]]??0;
            $item = [
                "name" => $k,
                "department" => $v[0]["department"],
                "deal_rate" => $totalAmountAll==0?"0%":(round($cusTotalAmount / $totalAmountAll, 2) * 100) . "%",
                "total_amount" => $cusTotalAmount,
                "total_count" => count($v),
                "write_count" => collect($v)->where("category_id", "in", [9])->count(),
                "reduce_repeat_count" => collect($v)->where("category_id", "in", [7,8,10])->count(),
                "other_count" => collect($v)->whereNotIn("category_id", [7,8,9,10])->count(),
                "deposit" => $depositData[$k]??0,
                "final_payment" => $finalPaymentData[$k]??0,
                "refund_amount" => $refundData[$k]??0,
                "customer_id" => $v[0]["customer_id"],
                "day_count" => $entryDays==0?0:round(count($v) / $entryDays, 1),
                "day_amount" => $entryDays==0?0:floatval(round($cusTotalAmount / $entryDays, 2)),
                "entry_days" => $entryDays
            ];
            $retData[] = $item;
        }

        $deposit = array_combine(array_column($_depositData, "name"), array_column($_depositData, "customer_id"));
        $final = array_combine(array_column($_finalPayment, "name"), array_column($_finalPayment, "customer_id"));
        $refund = array_combine(array_column($_refund, "name"), array_column($_refund, "customer_id"));
        $tail = array_merge($final, $refund, $deposit);
        $department = array_merge(array_combine(array_column($_depositData, "name"), array_column($_depositData, "department")), array_combine(array_column($_finalPayment, "name"), array_column($_finalPayment, "department")), array_combine(array_column($_refund, "name"), array_column($_refund, "department")));
        $keys = array_keys($tmp);
        foreach ($tail as $k => $v) {
            if (!in_array($k, $keys)) {
                $cusTotalAmount = floatval(round(($depositData[$k]??0) + ($finalPaymentData[$k]??0), 2));
                $entryDays = $entryTime[$v]??0;
                $item = [
                    "name" => $k,
                    "department" => $department[$k],
                    "deal_rate" => $totalAmountAll==0?"0%":(round($cusTotalAmount / $totalAmountAll, 2) * 100) . "%",
                    "total_amount" => $cusTotalAmount,
                    "total_count" => 0,
                    "write_count" => 0,
                    "reduce_repeat_count" => 0,
                    "other_count" => 0,
                    "deposit" => $depositData[$k]??0,
                    "final_payment" => $finalPaymentData[$k]??0,
                    "refund_amount" => $refundData[$k]??0,
                    "customer_id" => $v,
                    "day_count" => 0,
                    "day_amount" => $entryDays==0?0:floatval(round($cusTotalAmount / $entryDays, 2)),
                    "entry_days" => $entryDays
                ];
                $retData[] = $item;
            }
        }
        #上工日均冠比
        $day_sort = array_column($retData, "day_amount");
        array_multisort($day_sort, SORT_DESC, $retData);
        $day_max = $retData[0]["day_amount"]??0;
        #入账冠比
        $sort = array_column($retData, "total_amount");
        array_multisort($sort, SORT_DESC, $retData);
        $max = $retData[0]["total_amount"]??0;
        foreach ($retData as $k => $v) {
            $retData[$k]["rank"] = $k+1;
            $retData[$k]["champion_ratio"] = $max==0?"0%":floatval(round(($v["total_amount"] / $max) * 100, 2))."%";
            $retData[$k]["day_champion_ratio"] = $day_max==0?"0%":floatval(round(($v["day_amount"] / $day_max) * 100, 2))."%";
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

        # 定金
        $mapper = new OrdersMainMapper();
        $map = [["od.create_time", ">=", $startTime], ["od.create_time", "<=", $endTime], ["om.customer_id", "=", $param["customer_id"]]];
        $_depositData = $mapper->customerDetailDeposit($map);

        # 尾款
        $_finalPayment = $mapper->customerDetailFinal($map);
        # 退款
        $map = [["od.refund_time", ">=", $startTime], ["od.refund_time", "<=", $endTime], ["om.customer_id", "=", $param["customer_id"]]];
        $_refund = $mapper->customerDetailRefund($map);

        $data = ["deposit" => $_depositData, "final_payment" => $_finalPayment, "refund" => $_refund];
        $retData = [];
        foreach ($data as $k => $v) {
            foreach ($v as $key => $val) {
                $item = [
                    "origin_name" => $val["origin_name"],
                    "deposit" => floatval($k=='deposit'?$val["deposit"]:0),
                    "final_payment" => floatval($k=='final_payment'?$val["final_payment"]:0),
                    "refund_amount" => floatval($k=='refund'?$val["refund_amount"]:0),
                    "amount_time" => date("Y-m-d H", $val['create_time']),
                    "order_sn" => $val["order_sn"]
                ];
                $retData[] = $item;
            }

        }
        return $retData;
    }


    /**
     * 导出客服订单业绩
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
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

    /**
     * 导出客服订单业绩详情
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function exportDetail($param) {
        $data = $this->cusOrderPerfDetailBi($param);
        $header = [
            ["来源", "origin_name"],
            ["定金", "deposit"],
            ["尾款", "final_payment"],
            ["退款", "refund_amount"],
            ["收款时间", "amount_time"],
            ["订单编号", "order_sn"],
        ];
        return Excel::exportData($data, $header, "客服订单业绩详情数据");
    }
}
