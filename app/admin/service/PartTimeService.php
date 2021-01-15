<?php


namespace app\admin\service;


use app\mapper\EngineerMapper;
use app\mapper\OrdersMapper;
use app\mapper\PartTimeMapper;
use app\mapper\UserMapper;

class PartTimeService extends BaseService
{
    protected $mapper = PartTimeMapper::class;

    /**
     * 兼职数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function partTimes($param) {
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["pt.create_time", ">=", strtotime($param["range_time"][0])],
                ["pt.create_time", "<=", strtotime($param["range_time"][1])]
            ];
            $map = [
                ["create_time", ">=", strtotime($param["range_time"][0])],
                ["create_time", "<=", strtotime($param["range_time"][1])]
            ];
            $billMap = [
                ["bill_time", ">=", strtotime($param["range_time"][0])],
                ["bill_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["pt.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["pt.create_time", "<=", time()],
            ];
            $map = [
                ["create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["create_time", "<=", time()],
            ];
            $billMap = [
                ["bill_time", ">=", strtotime(date("Y-m-1", time()))],
                ["bill_time", "<=", time()],
            ];
        }
        if (isset($param["user_id"]) && !empty($param["user_id"])) {
            $where[] = ["u.id", "=", $param["user_id"]];
        }
        # 获取招聘人数
        $perCount = (new EngineerMapper())->columnBy($map, "personnel_id");
        $perCount = array_count_values($perCount);
        # 获取发单数量
        $orderCount = (new OrdersMapper())->columnBy($billMap, "biller");
        $orderCount = array_count_values($orderCount);
        # 启用人数
        $turnOnCount = (new EngineerMapper())->turnOnCount($billMap);
        $turnOnCount = array_count_values($turnOnCount);

        $data = (new PartTimeMapper())->partTimes($where);
        $tmp = [];
        foreach ($data as $k => $v)
            $tmp[$v["user_id"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $workTime = array_sum(array_column($v, "work_time"));
            $errOrderCount = array_sum(array_column($v, "err_order_count"));
            $otherFee = array_sum(array_column($v, "other_fee"));
            $fine = array_sum(array_column($v, "fine"));
            if ($v[0]["work_nature"] == 0) {
                $totalSalary = floatval(round($v[0]["salary"] * $workTime, 2));
            }else {
                $totalSalary = floatval(round($v[0]["salary"] * ($orderCount[$k]??0), 2));
            }
            $cost = floatval(round($totalSalary + $otherFee - $fine, 2));
            $item = [
                "user_id" => $k,
                "name" => $v[0]["name"],
                "work_nature_text" => $v[0]["work_nature"]==0?"坐班兼职":"坐班兼职",
                "work_nature" => $v[0]["work_nature"],
                "work_time" => $workTime,
                "recruitment_count" => $perCount[$k]??0,
                "bill_count" => $orderCount[$k]??0,
                "err_order_count" => $errOrderCount,
                "err_order_ratio" => (($orderCount[$k]??0) == 0)?"0%":floatval(round(($errOrderCount / ($orderCount[$k]??0)) * 100, 2))."%",
                "turn_on_count" => $turnOnCount[$k]??0,
                "salary" => floatval($v[0]["salary"]),
                "totalSalary" => floatval($totalSalary),
                "other_fee" => floatval($otherFee),
                "fine" => floatval($fine),
                "cost" => floatval($cost),
                "turn_on_cost" => (($turnOnCount[$k]??0)==0)?0:floatval(round($cost / ($turnOnCount[$k]??0), 2)),
                "order_cost" => (($orderCount[$k]??0)==0)?0:floatval(round($cost / ($orderCount[$k]??0), 2))
            ];
            $retData[] = $item;
        }
        return $retData;
    }

    /**
     * 更新薪水
     * @param $param
     * @return mixed
     */
    public function updateSalary($param) {
        return (new UserMapper())->updateWhere(["id" => $param["user_id"]], ["salary" => $param["salary"]]);
    }

    /**
     * 兼职详情
     * @param $param
     * @return mixed
     */
    public function partTimeDetail($param) {
        $where = [
            ["user_id", "=", $param["user_id"]],
        ];
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where[] = ["create_time", ">=", strtotime($param["range_time"][0])];
            $where[] = ["create_time", "<=", strtotime($param["range_time"][1])];
        }else {
            $where[] = ["create_time", ">=", strtotime(date("Y-m-1", time()))];
            $where[] = ["create_time", "<=", time()];
        }
        $data = $this->selectBy($where, "*", "create_time desc");
        foreach ($data as $k => $v) {
            $data[$k]["work_time"] = floatval($v["work_time"]);
            $data[$k]["other_fee"] = floatval($v["other_fee"]);
            $data[$k]["fine"] = floatval($v["fine"]);
        }
        return $data;
    }

    /**
     * 更新兼职信息
     * @param $param
     * @return mixed
     */
    public function updatePartTime($param) {
        return $this->updateWhere(["id" => $param["id"]], [$param["field"] => $param["value"]]);
    }
}
