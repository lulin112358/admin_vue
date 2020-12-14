<?php


namespace app\admin\service;


use app\mapper\AccountMapper;
use app\mapper\OrdersMainMapper;

class CustomerBiService
{
    /**
     * 获取
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerBiCount() {
        $where = [];
        $sortCols = $this->accountColSort();
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
    public function accountColSort() {
        $where = [];
        # 获取所有来源
        $accountData = (new AccountMapper())->selectBy(["status" => 1], "id, simple_name as title, concat('account_', id) as field");
        $accounts = array_column($accountData, "id");
        # 获取所有来源排序
        $accountSort = (new OrdersMainMapper())->accountSortData($where);
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
        array_unshift($accountData, ["title" => "笔数", "field" => "total", "showOverflow" => true, "showHeaderOverflow" => true, "showFooterOverflow" => true, "minWidth" => "100px"]);
        array_unshift($accountData, ["title" => "姓名", "field" => "customer_name", "showOverflow" => true, "showHeaderOverflow" => true, "showFooterOverflow" => true, "minWidth" => "100px"]);
        return $accountData;
    }
}
