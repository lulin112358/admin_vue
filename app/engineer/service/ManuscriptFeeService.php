<?php


namespace app\engineer\service;


use app\BaseService;
use app\mapper\EngineerErrMapper;
use app\mapper\OrdersMapper;
use app\mapper\SettlementLogMapper;
use Carbon\Carbon;
use excel\Excel;
use jwt\Jwt;

class ManuscriptFeeService extends BaseService
{
    # 订单状态
    private $status = [
        1 => "审核中",
        2 => "审核中",
        3 => "已交稿",
        4 => "客户准备退款",
        5 => "客户已退款",
        6 => "审核中",
        7 => "审核中",
        8 => "返修中"
    ];

    public function manuscriptFee($param) {
        if (isset($param["token"]) && !empty($param["token"]))
            request()->uid = Jwt::decodeToken($param["token"])["data"]->uid;

        $where = [];
        if (isset($param["search_key"]) && !empty($param["search_key"]))
            $where[] = ["o.order_sn", "like", "%{$param['search_key']}%"];
        $data = (new OrdersMapper())->engineerManuscript(request()->uid, $where);
        foreach ($data as $k => $v) {
            $data[$k]["remain_fee"] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            $data[$k]["status_text"] = $this->status[$v["status"]];
            $data[$k]["check_status"] = $v["is_check"]==1?"已核对":"未核对";
            $data[$k]["err_status_text"] = $v["err_status"]==1?"已处理":(is_null($v['err_status'])?"未报错":"未处理");
            $data[$k]["check_color"] = $v["is_check"]==1?"green":"yellow";
            $data[$k]["err_color"] = $v["err_status"]==1?"green":(is_null($v['err_status'])?"":"yellow");
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
            $data[$k]["actual_delivery_time"] = $v["actual_delivery_time"] == 0 ? "审核中" :date("Y-m-d H", $v["actual_delivery_time"]);
            $data[$k]["manuscript_fee"] = floatval($v["manuscript_fee"]);
            $data[$k]["settlemented"] = floatval($v["settlemented"]);
            $data[$k]["deduction"] = floatval($v["deduction"]);
            # 计算预计结算时间
//            $time = $v["actual_delivery_time"]==0 ? $v["delivery_time"] : (($v["delivery_time"] > $v["actual_delivery_time"]) ? $v["actual_delivery_time"] : $v["delivery_time"]);
//            $data[$k]["time"] = $time;
            $time = Carbon::parse(date("Y-m-d H:i:s", $data[$k]["time"]));
            $time = $time->addDays(10);
            $canTime = Carbon::parse(date("Y-m-d H:i:s", time()));
            $day = $time->day;
            $canDay = $canTime->day;
            if ($day >= 1 && $day <= 10) {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . '10';
            } else if ($day > 10 && $day <= 20) {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . '20';
            } else {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . $time->daysInMonth;
            }
            if ($canDay >= 1 && $canDay <= 10) {
                $canSettlementTime = $canTime->year . '-' . $canTime->month . '-' . '10';
            } else if ($canDay > 10 && $canDay <= 20) {
                $canSettlementTime = $canTime->year . '-' . $canTime->month . '-' . '20';
            } else {
                $canSettlementTime = $canTime->year . '-' . $canTime->month . '-' . $canTime->daysInMonth;
            }
            # 判断是否可结算设置颜色
            if (strtotime($data[$k]["settlement_time"]) <= strtotime($canSettlementTime)) {
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
//        $data = collect($data)->order("time")->toArray();
        return $data;
    }

    /**
     * 确认核定
     * @param $param
     * @return mixed
     */
    public function confirmManuscript($param) {
        # 查询该订单状态，判断是否可核定
        $mapper = new OrdersMapper();
        $data = $mapper->selectBy(["id" => $param["id"]]);
        $flag = true;
        foreach ($data as $k => $v) {
            $time = ($v["delivery_time"] > $v["actual_delivery_time"]) ? $v["actual_delivery_time"] : $v["delivery_time"];
            $time = Carbon::parse(date("Y-m-d H:i:s", $time));
            $time = $time->addDays(10);
            $canTime = Carbon::parse(date("Y-m-d H:i:s", time()));
            $canDay = $canTime->day;
            $day = $time->day;
            if ($day >= 1 && $day <= 10) {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . '10';
            } else if ($day > 10 && $day <= 20) {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . '20';
            } else {
                $data[$k]["settlement_time"] = $time->year . '-' . $time->month . '-' . $time->daysInMonth;
            }

            if ($canDay >= 1 && $canDay <= 10) {
                $canSettlementTime = $canTime->year . '-' . $canTime->month . '-' . '10';
            } else if ($canDay > 10 && $canDay <= 20) {
                $canSettlementTime = $canTime->year . '-' . $canTime->month . '-' . '20';
            } else {
                $canSettlementTime = $canTime->year . '-' . $canTime->month . '-' . $canTime->daysInMonth;
            }

            if (strtotime($data[$k]["settlement_time"]) > strtotime($canSettlementTime)) {
                $flag = false;
            }
        }
        if (!$flag)
            return false;

        return (new OrdersMapper())->updateWhere(["id" => $param["id"]], ["is_check" => 1]);
    }

    /**
     * 结算记录
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function settlementLog($param) {
        if (isset($param["token"]) && !empty($param["token"]))
            request()->uid = Jwt::decodeToken($param["token"])["data"]->uid;

        $where = [["o.engineer_id", "=", request()->uid]];
        if (isset($param["search_key"]) && !empty($param["search_key"])) {
            $where[] = ["o.order_sn", "like", "%{$param['search_key']}%"];
        }
        if (isset($param["create_time"]) && !empty($param["create_time"])) {
            $where[] = ["sl.create_time", ">=", strtotime($param["create_time"][0])];
            $where[] = ["sl.create_time", "<=", strtotime($param["create_time"][1])];
        }
        $data = (new SettlementLogMapper())->settlementLogs($where);
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["settlement_fee"] = floatval($v["settlement_fee"]);
        }
        return $data;
    }

    /**
     * 稿费报错
     * @param $param
     * @return mixed
     */
    public function errSubmit($param) {
        return (new EngineerErrMapper())->add($param);
    }

    /**
     * 稿费报错更新
     * @param $param
     * @return mixed
     */
    public function errUpdate($param) {
        return (new EngineerErrMapper())->updateWhere(["order_id" => $param["order_id"]], ["err" => $param["err"]]);
    }

    /**
     * 导出
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export($param) {
        $data = $this->manuscriptFee($param);
        $header = [
            ["订单编号", "order_sn"],
            ["要求", "require"],
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
            ["核对状态", "check_status"],
        ];
        return Excel::exportData($data, $header, "稿费数据");
    }

    /**
     * 导出结算记录
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function exportLog($param) {
        $data = $this->settlementLog($param);
        $header = [
            ["订单编号", "order_sn"],
            ["结算金额", "settlement_fee"],
            ["结算时间", "create_time"]
        ];
        return Excel::exportData($data, $header, "稿费结算记录数据");
    }
}
