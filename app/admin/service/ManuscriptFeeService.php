<?php


namespace app\admin\service;


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


    /**
     * 获取工程师稿费结算情况
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manuscriptFees() {
        // 设置中文
        Carbon::setLocale("zh");
        $now = Carbon::now();
        $day = $now->day;
        $month = $now->month;
        $year = $now->year;
        if ($day >=1 && $day <= 10) {
            $day = 10;
        } elseif ($day > 10 && $day <= 20) {
            $day = 20;
        }else {
            $day = $now->daysInMonth;
        }
        $data = (new OrdersMapper())->manuscripts();
        $canSettlement = collect($data)->where("delivery_time", "<=", time())->toArray();
        $canSettlementData = [];
        foreach ($canSettlement as $k => $v) {
            $canSettlementData[$v["engineer_id"]] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
        }
        foreach ($data as $k => $v) {
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
        $data = (new OrdersMapper())->engineerDetail($param["engineer_id"]);
        foreach ($data as $k => $v) {
            $data[$k]["remain_fee"] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            $data[$k]["status_text"] = $this->status[$v["status"]];
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
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
}
