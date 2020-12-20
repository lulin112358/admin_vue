<?php


namespace app\admin\service;


use app\mapper\OrdersMapper;
use Carbon\Carbon;
use excel\Excel;

class ManuscriptFeeService extends BaseService
{
    # 订单状态
    private $status = [
        1 => "未发出",
        2 => "已发出",
        3 => "已交稿",
        4 => "准备退款",
        5 => "已退款",
        6 => "已发全能"
    ];


    /**
     * 获取工程师稿费结算情况
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manuscriptFees($param) {
        // 设置中文
        Carbon::setLocale("zh");
        $where = [];
        if (isset($param["search_key"]) && !empty($param["search_key"])) {
            $where[] = ["e.qq_nickname|e.contact_qq|o.order_sn", "like", "%{$param["search_key"]}%"];
        }
        if (isset($param["delivery_time"]) && !empty($param["delivery_time"])) {
            $where[] = ["o.delivery_time", ">=", strtotime($param["delivery_time"][0])];
            $where[] = ["o.delivery_time", "<=", strtotime($param["delivery_time"][1])];
        }
        $data = (new OrdersMapper())->manuscripts($where);
        $canSettlement = collect($data)->where("delivery_time", "<=", time())->toArray();
        $canSettlementData = [];
        foreach ($canSettlement as $k => $v) {
            $canSettlementData[$v["engineer_id"]] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
        }
        foreach ($data as $k => $v) {
            $carbonObj = Carbon::parse(date("Y-m-d H:i:s", $v["delivery_time"]));
            $carbonObj = $carbonObj->addDays(10);
            $year = $carbonObj->year;
            $month = $carbonObj->month;
            $day = $carbonObj->day;
            if ($day >=1 && $day <= 10) {
                $day = 10;
            } elseif ($day > 10 && $day <= 20) {
                $day = 20;
            }else {
                $day = $carbonObj->daysInMonth;
            }
            $data[$k]["settlement_time"] = $year.'-'.$month.'-'.$day;
            $data[$k]["manuscript_fee"] = $canSettlementData[$v["engineer_id"]]??0;
            $data[$k]["deduction"] = floatval($v["deduction"]);
            $data[$k]["settlemented"] = floatval($v["settlemented"]);
            $data[$k]["remain_fee"] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            $data[$k]["rate"] = $data[$k]["remain_fee"] == 0 ? 0 : ($data[$k]["manuscript_fee"] / $data[$k]["remain_fee"]) * 100;
        }
        # 按照应结率 / 应结降序排序
        $sortField = array_column($data, 'rate');
        array_multisort($sortField, SORT_DESC, $data);
        $sortField1 = array_column($data, 'manuscript_fee');
        array_multisort($sortField1, SORT_DESC, $data);
        return $data;
    }


    /**
     * 获取工程师稿费结算详情信息
     *
     * @param $param
     * @return mixed
     */
    public function engineerDetail($param)
    {
        $where = [];
        if (isset($param["order_sn"]) && !empty($param["order_sn"]))
            $where[] = ["o.order_sn", "like", "%{$param['order_sn']}%"];
        $data = (new OrdersMapper())->engineerDetail($param["engineer_id"], $where);
        foreach ($data as $k => $v) {
            $data[$k]["remain_fee"] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            $data[$k]["status_text"] = $this->status[$v["status"]];
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
            $data[$k]["actual_delivery_time"] = $v["actual_delivery_time"] == 0 ? "暂未交稿" :date("Y-m-d H", $v["actual_delivery_time"]);
            $data[$k]["manuscript_fee"] = floatval($v["manuscript_fee"]);
            $data[$k]["settlemented"] = floatval($v["settlemented"]);
            $data[$k]["deduction"] = floatval($v["deduction"]);
            # 计算预计结算时间
            $time = Carbon::parse(date("Y-m-d H:i:s", $v["delivery_time"]));
            $time = $time->addDays(10);
            $day = $time->day;
            if ($day >= 1 && $day <= 10) {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . '10';
            } else if ($day > 10 && $day <= 20) {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . '20';
            } else {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . $time->daysInMonth;
            }
            # 判断是否可结算设置颜色
            if (strtotime($data[$k]["settlement_time"]) <= time()) {
                $data[$k]["color"] = "green";
            } else {
                $data[$k]["color"] = "yellow";
            }
            # 结算状态
            if ($v["status"] == 5) {
                $settlementStatus = "不合格已退款不结算";
            }
            if ($v["settlemented"] == 0) {
                $settlementStatus = "未结算";
            }
            if ($data[$k]["remain_fee"] == 0) {
                $settlementStatus = "已结算";
            }
            if ($v["settlemented"] != 0 && $data[$k]["remain_fee"] != 0) {
                $settlementStatus = "部分结算";
            }
            $data[$k]["settlement_status"] = $settlementStatus;
        }
        # 按照未结算降序排列
        $sortField = array_column($data, 'remain_fee');
        array_multisort($sortField, SORT_DESC, $data);
        return $data;
    }


    /**
     * 导出
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($param) {
        $_data = $this->manuscriptFees($param);
        foreach ($_data as $k => $v)
            $_data[$k]["rate"] = $v["rate"]."%";
        $header = [
            ["编辑", "qq_nickname"],
            ["应结", "manuscript_fee"],
            ["未发总计", "remain_fee"],
            ["应结率", "rate"],
            ["应结时间", "settlement_time"]
        ];
        return Excel::exportData($_data, $header, "编辑稿费数据");
    }

    /**
     * 导出详情
     *
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportDetail($param) {
        $_data = $this->engineerDetail($param);
        $header = [
            ["订单编号", "order_sn"],
            ["业务分类", "cate_name"],
            ["稿费预计发放时间", "settlement_time"],
            ["稿费", "manuscript_fee"],
            ["已结算", "settlemented"],
            ["未结算", "remain_fee"],
            ["扣款", "deduction"],
            ["预计交稿时间", "delivery_time"],
            ["实际交稿时间", "actual_delivery_time"],
            ["结算状态", "settlement_status"],
            ["订单状态", "status_text"],
        ];
        foreach ($_data as $k => $v) {
            if ($v["actual_delivery_time"] == 0) {
                $_data[$k]["actual_delivery_time"] = "暂未交稿";
            }
        }
        return Excel::exportData($_data, $header, "编辑稿费数据");
    }
}
