<?php


namespace app\admin\service;


use app\mapper\AttendanceMapper;

class AttendanceService extends BaseService
{
    protected $mapper = AttendanceMapper::class;

    private $type = [
        1 => "正常上班",
        2 => "提前上班",
        3 => "迟到",
        4 => "提前请假",
        5 => "意外请假",
        6 => "日内短假",
        7 => "半天请假"
    ];

    private $color = [
        1 => "green",
        2 => "green",
        3 => "red",
        4 => "red",
        5 => "red",
        6 => "red",
        7 => "red"
    ];

    /**
     * 获取考勤数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function attendances($param) {
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["a.create_time", ">=", strtotime($param["range_time"][0])],
                ["a.create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["a.create_time", ">=", strtotime(date("Y-m-1", time()))],
                ["a.create_time", "<=", time()],
            ];
        }
        $data = (new AttendanceMapper())->attendances($where);
        $tmp = [];
        foreach ($data as $k => $v)
            $tmp[$v["name"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $dataCollect = collect($v);
            # 出勤考勤
            $attendanceCount = $dataCollect->whereIn("type", [1, 2, 3, 6])->count();
            $attendanceCount = floatval($dataCollect->where("type", "=", 7)->count() / 2 + $attendanceCount);
            # 迟到信息
            $lateInfo = $dataCollect->where("type", 3)->toArray();
            # 迟到次数
            $lateCount = count($lateInfo);
            # 迟到时长
            $lateTime = array_sum(array_column($lateInfo, "late_time"));
            # 请假天数
            $leaveCount = $dataCollect->whereIn("type", [4, 5])->count();
            $leaveCount = floatval($dataCollect->where("type", "=", 6)->count() / 2 + $leaveCount);
            # 意外请假
            $accidentCount = $dataCollect->where("type", "=", 5)->count();
            $item = [
                "user_id" => $v[0]["user_id"],
                "name" => $k,
                "department" => $v[0]["department"],
                "attendance_count" => $attendanceCount,
                "late_count" => $lateCount,
                "late_time" => $lateTime,
                "leave_count" => $leaveCount,
                "reward" => floatval(array_sum(array_column($v, "reward"))),
                "attendance_rate" => (floatval(round($attendanceCount / count($v), 2)) * 100) . "%",
                "accident_rate" => (floatval(round($accidentCount / count($v), 2)) * 100) . "%",
            ];
            $retData[] = $item;
        }
        return $retData;
    }

    /**
     * 获取用户考勤信息
     * @param $param
     * @return mixed
     */
    public function userAttendances($param) {
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
        $data = $this->selectBy($where, "id, type, result, late_time, note, reward, create_time");
        foreach ($data as $k => $v) {
            $data[$k]["type"] = $this->type[$v["type"]];
            $data[$k]["color"] = $this->color[$v["type"]];
        }
        return $data;
    }
}
