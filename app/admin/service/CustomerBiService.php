<?php


namespace app\admin\service;


use app\mapper\AccountMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\UserMapper;
use Carbon\Carbon;

class CustomerBiService
{
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
                ["om.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["om.create_time", "<=", time()],
            ];
        }
        $type = $param["type"]??1;
        $sortCols = $this->accountColSort($where, $type);
        $fields = array_column($sortCols, "field");
        # 查找客服业绩
        $customerData = (new OrdersMainMapper())->customerData($where);
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
                $total = 0;
                foreach ($v as $idx => $val) {
                    if (!empty($idx) && in_array("account_".$idx, $fields)) {
                        $amount = array_sum(array_column($val, "total_amount"));
                        $item["account_".$idx] = $amount;
                        $total += $amount;
                    }
                }
                $item["total_amount"] = $total;
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
        }
        $accountSort = array_column($accountSort, "account_id");
        $_accountData = [];
        foreach ($accountData as $k => $v) {
            if (!in_array($v["id"], $accountSort)) {
                $accountSort[] = $v["id"];
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
                ["om.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["om.create_time", "<=", time()],
            ];
            $days = (new Carbon())->diffInDays(Carbon::parse(date("Y-m-1", time())));
        }
        # 查找客服业绩
        $customerData = (new OrdersMainMapper())->customerOrderData($where);
        # 客服入职日期
        $userData = (new UserMapper())->userData(["id" => array_column($customerData, "customer_id")]);
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
            $totalCount = 0;
            $totalAmount = 0;
            $totalCount += count($v);
            $totalAmount += array_sum(array_column($v, "total_amount"));
            $item = [
                "customer_name" => $k,
                "total_count" => $totalCount,
                "total_amount" => $totalAmount,
                "day_count" => round($totalCount / $days, 1),
                "day_amount" => floatval(round($totalAmount / $days, 2)),
                "entry_days" => (new Carbon())->diffInDays(Carbon::parse($entryTime[$v[0]["customer_id"]]))
            ];
            $retData[] = $item;
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
                ["create_time", ">=", strtotime($param["range_time"][0])],
                ["create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["create_time", "<=", time()],
            ];
        }
        # 查找客服业绩
        $customerData = (new OrdersMainMapper())->cusOrderPerfData($where);
        $totalAmountAll = array_sum(array_column($customerData, "total_amount"));
        $tmp = [];
        foreach ($customerData as $k => $v)
            $tmp[$v["customer_name"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $totalAmount = array_sum(array_column($v, "total_amount"));
            $item = [
                "name" => $k,
                "deal_rate" => (round($totalAmount / $totalAmountAll, 2) * 100) . "%",
                "total_amount" => $totalAmount,
                "total_count" => count($v),
                "write_count" => collect($v)->where("category_id", "in", [9])->count(),
                "reduce_repeat_count" => collect($v)->where("category_id", "in", [7,8,10])->count(),
                "other_count" => collect($v)->whereNotIn("category_id", [7,8,9,10])->count(),
                "deposit" => array_sum(array_column($v, "deposit")),
                "final_payment" => array_sum(array_column($v, "final_payment")),
                "refund_amount" => array_sum(array_column($v, "refund_amount")),
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
