<?php


namespace app\engineer\service;


use app\BaseService;
use app\mapper\OrdersMapper;
use Carbon\Carbon;

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

    public function manuscriptFee() {
        $where = [];
        if (isset($param["order_sn"]) && !empty($param["order_sn"]))
            $where[] = ["o.order_sn", "like", "%{$param['order_sn']}%"];
        $data = (new OrdersMapper())->engineerDetail(request()->uid, $where);
        foreach ($data as $k => $v) {
            $data[$k]["remain_fee"] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            $data[$k]["status_text"] = $this->status[$v["status"]];
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
            $data[$k]["actual_delivery_time"] = $v["actual_delivery_time"] == 0 ? "暂未交稿" :date("Y-m-d H", $v["actual_delivery_time"]);
            $data[$k]["manuscript_fee"] = floatval($v["manuscript_fee"]);
            $data[$k]["settlemented"] = floatval($v["settlemented"]);
            $data[$k]["deduction"] = floatval($v["deduction"]);
            # 计算预计结算时间
            $time = ($v["delivery_time"] > $v["actual_delivery_time"]) ? $v["actual_delivery_time"] : $v["delivery_time"];
            $time = Carbon::parse(date("Y-m-d H:i:s", $time));
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
        $sortField = array_column($data, 'settlement_time');
        array_multisort($sortField, SORT_ASC, $data);
        return $data;
    }
}
