<?php


namespace app\admin\service;


use app\mapper\AttendanceMapper;
use app\mapper\PartTimeMapper;
use app\mapper\UserMapper;
use app\mapper\UserRoleMapper;
use app\mapper\VacationMapper;
use Carbon\Carbon;
use excel\Excel;
use jwt\Jwt;

class AttendanceService extends BaseService
{
    protected $mapper = AttendanceMapper::class;

    private $type = [
        0 => "未出勤",
        1 => "正常上班",
        2 => "提前上班",
        3 => "迟到",
        4 => "提前请假",
        5 => "意外请假",
        6 => "日内短假",
        7 => "半天请假"
    ];

    private $color = [
        0 => "red",
        1 => "green",
        2 => "green",
        3 => "red",
        4 => "red",
        5 => "red",
        6 => "red",
        7 => "red"
    ];

    private $result = [
        0 => 0,
        1 => 1,
        2 => 1,
        3 => 1,
        4 => 0,
        5 => 0,
        6 => 1,
        7 => 0.5
    ];

    /**
     * 获取考勤数据
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function attendances($param, $export = false) {
        if ($export) {
            request()->uid = Jwt::decodeToken($param["token"])["data"]->uid;
        }
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            $where = [
                ["a.create_time", ">=", strtotime($param["range_time"][0])],
                ["a.create_time", "<=", strtotime($param["range_time"][1])]
            ];
        }else {
            $where = [
                ["a.create_time", ">=", strtotime(date("Y-m-d", time()))],
                ["a.create_time", "<=", time()],
            ];
        }
        # 判断该用户是否为人事或者管理层
        $auth = (new UserRoleMapper())->columnBy(["user_id" => request()->uid], "role_id");
        if (!(in_array(1, $auth) || in_array(13, $auth))) {
            $where[] = ["u.id", "=", request()->uid];
        }else{
            if (isset($param["user_id"]) && !empty($param["user_id"])) {
                $where[] = ["u.id", "=", $param["user_id"]];
            }
            if (isset($param["department_code"]) && !empty($param["department_code"])) {
                $where[] = ["u.department_code", "=", $param["department_code"]];
            }
        }
        $data = (new AttendanceMapper())->attendances($where);
        $tmp = [];
        foreach ($data as $k => $v)
            $tmp[$v["name"]][] = $v;

        $retData = [];
        foreach ($tmp as $k => $v) {
            $dataCollect = collect($v);
            # 出勤考勤
            $attendanceCount = array_sum($dataCollect->column("result"));
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
                "id" => $v[0]["id"],
                "name" => $k,
                "department" => $v[0]["department"],
                "attendance_count" => $attendanceCount,
                "late_count" => $lateCount,
                "late_time" => $lateTime,
                "late_time_text" => $lateTime.'分',
                "check_in_time" => $v[0]["check_in_time"]==0?"未出勤":date("Y-m-d H:i:s", $v[0]["check_in_time"]),
                "check_out_time" => $v[0]["check_out_time"]==0?"未签退":date("Y-m-d H:i:s", $v[0]["check_out_time"]),
                "check_in_timestamp" => $v[0]["check_in_time"],
                "check_out_timestamp" => $v[0]["check_out_time"],
                "type" => $this->type[$v[0]["type"]],
                "color" => $this->color[$v[0]["type"]],
                "work_time" => array_sum(array_column($v, "work_time")),
                "work_time_text" => array_sum(array_column($v, "work_time")).'时',
                "leave_count" => $leaveCount,
                "reward" => floatval(array_sum(array_column($v, "reward"))),
                "count" => count($v),
                "attendance_rate" => (floatval(round($attendanceCount / count($v), 2)) * 100) . "%",
                "accident_rate" => (floatval(round($accidentCount / count($v), 2)) * 100) . "%",
                "accident" => $accidentCount,
            ];
            $retData[] = $item;
        }
        return $retData;
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
        $data = $this->attendances($param, true);
        $header = [
            ["姓名", "name"],
            ["部门-职位", "department"],
            ["出勤性质", "type"],
            ["签到时间", "check_in_time"],
            ["签退时间", "check_out_time"],
            ["工作时长", "work_time_text"],
            ["出勤", "attendance_count"],
            ["迟到次数", "late_count"],
            ["迟到时长", "late_time_text"],
            ["请假天数", "leave_count"],
            ["出勤率", "attendance_rate"],
            ["意外率", "accident_rate"],
            ["奖惩", "reward"],
        ];
        if (isset($param["range_time"]) && !empty($param["range_time"])) {
            if (date("Y-m-d 00:00:00") != $param["range_time"][0]) {
                $header = [
                    ["姓名", "name"],
                    ["部门-职位", "department"],
                    ["工作时长", "work_time_text"],
                    ["出勤", "attendance_count"],
                    ["迟到次数", "late_count"],
                    ["迟到时长", "late_time"],
                    ["请假天数", "leave_count"],
                    ["出勤率", "attendance_rate"],
                    ["意外率", "accident_rate"],
                    ["奖惩", "reward"],
                ];
            }
        }
        return Excel::exportData($data, $header, "考勤数据");
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
        $data = $this->selectBy($where, "*", "create_time desc");
        foreach ($data as $k => $v) {
            $data[$k]["type"] = $this->type[$v["type"]];
            $data[$k]["color"] = $this->color[$v["type"]];
            $data[$k]["check_in_time"] = $v["check_in_time"]==0?"未出勤":date("Y-m-d H:i:s", $v["check_in_time"]);
            $data[$k]["check_out_time"] = $v["check_out_time"]==0?"未签退":date("Y-m-d H:i:s", $v["check_out_time"]);
        }
        return $data;
    }


    /**
     * 更新考勤信息
     * @param $param
     * @return mixed
     */
    public function updateAttendance($param) {
        if ($param["field"] == "late_time_text") {
            $param["field"] = "late_time";
            $param["value"] = strstr($param["value"], "分")?explode("分", $param["value"])[0]:$param["value"];
        }
        $updateData = [
            "id" => $param["id"],
            $param["field"] => $param["value"]
        ];
        if ($param["field"] == "type") {
            $updateData["result"] = $this->result[$param["value"]];
            if ($param["value"] == 5) {
                $updateData["reward"] = -10;
            }
        }
        return $this->updateBy($updateData);
    }

    /**
     * 签到
     * @return mixed
     */
    public function checkIn() {
        Carbon::setLocale("zh");
        $carbon = new Carbon();
        # 获取该员工的上班时间
        $workTime = (new UserMapper())->workTime();
        # 获取该员工昨日下班时间
        $checkOutTime = $this->findBy([["user_id", "=", request()->uid], ["create_time", ">=", strtotime(date("Y-m-d", strtotime("-1 day")))], ["check_out_time", "<>", 0]], "check_out_time")["check_out_time"];
        $info = $this->findBy(["user_id" => request()->uid], "id, create_time", "create_time desc");
        $updateData = [
            "check_in_time" => time(),
            "type" => 1,
            "result" => 1,
            "reward" => 0
        ];
        # 迟到时间
        $late = strtotime(date("Y-m-d").' '.$workTime["start_time"]);
        # 如果该员工超过12点下班 则可以延迟一小时
        if (date("Y-m-d", $checkOutTime) == date("Y-m-d", time()))
            $late += 3600;
        # 迟到了
        if (time() > $late + 60) {
            $time = Carbon::parse(date("Y-m-d H:i:s", $late));
            $lateTime = $carbon->diffInMinutes($time);
            $result = 0.9;
            $reward = -10;
            if ($lateTime > 60 && $lateTime <= 60 * 4) {
                $result = 0.8;
            }
            if ($lateTime > 60 * 4) {
                $result = 0.5;
            }
            if($lateTime > 30 && $lateTime <= 60) {
                $reward = -20;
            }
            if ($lateTime > 60 && $lateTime <= 60 * 2) {
                $reward = -50;
            }
            if ($lateTime > 60 * 2 && $lateTime <= 60 * 4) {
                $reward = -100;
            }
            if ($lateTime > 60 * 4) {
                $reward = -200;
            }
            $updateData["late_time"] = $lateTime;
            $updateData["result"] = $result;
            $updateData["type"] = 3;
            $updateData["reward"] = $reward;
        }

        return $this->updateWhere(["id" => $info["id"]], $updateData);
    }

    /**
     * 签退
     * @return mixed
     */
    public function checkOut() {
        Carbon::setLocale("zh");
        $isCheckOut = (new AttendanceMapper())->checkOutInfo();
//        $isCheckOut = $this->findBy([["user_id", "=", request()->uid], ["create_time", "=", strtotime(date("Y-m-d 9:00:00", strtotime("-1 day")))], ["type", "not in", [4, 5]]], "check_out_time, create_time", "create_time desc");
//        if (date("Y-m-d", time()) != date("Y-m-d", strtotime($isCheckOut["create_time"]))) {
//            if ($isCheckOut && $isCheckOut["check_out_time"] == 0) {
//                throw new \Exception("昨天未签退 请刷新页面后签到");
//            }
//        }
        if (isset($isCheckOut[1])) {
            if ($isCheckOut[1]["check_out_time"] == 0 && $isCheckOut[0]["check_in_time"] == 0) {
                throw new \Exception("昨天未签退 请刷新页面后签到");
            }
        }
        $info = $this->findBy([["user_id", "=", request()->uid], ["check_in_time", "<>", 0]], "id, check_in_time", "create_time desc");
        $time = Carbon::parse($info["check_in_time"]);
        $workTime = (new Carbon())->diffInHours($time);
        # 获取考勤结果
        $result = round($workTime / 8, 1);
        if ($result >= 1)
            $result = 1;
        return $this->updateWhere(["id" => $info["id"]], ["check_out_time" => time(), "work_time" => $workTime, "result" => $result]);
    }

    /**
     * 是否签到
     * @return bool
     */
    public function isCheckIn() {
        $info = $this->findBy(["user_id" => request()->uid], "id, check_in_time", "create_time desc");
        if ($info["check_in_time"] == 0) {
            return false;
        }else{
            return true;
        }
    }


    /**
     * 添加考勤记录
     */
    public function addData() {
        # 查询所有在职用户
        # 全职
        $userId = (new UserMapper())->columnBy(["status" => 1, "work_nature" => 1], "id");
        # 兼职
        $userId1 = (new UserMapper())->columnBy(["status" => 1, "work_nature" => [0, 2]], "id");
        # 查询提前请假记录
        $vacation = (new VacationMapper())->selectBy(["status" => 0]);
        # 添加考勤记录
        $data = [];
        foreach($userId as $k => $v) {
            $item = [
                "user_id" => $v,
                "type" => 0,
                "result" => 0,
                "create_time" => strtotime(date("Y-m-d 09:00:00", time()))
            ];
            foreach ($vacation as $key => $val) {
                if ($v == $val["user_id"] && $val["vacation_time"] == strtotime(date("Y-m-d 09:00:00", time()))) {
                    $item["type"] = $val["vacation_type"];
                    if ($val["vacation_type"] == 7) {
                        $item["result"] = 0.5;
                    }
                }
            }
            $data[] = $item;
        }
        $data1 = [];
        foreach($userId1 as $k => $v) {
            $item = [
                "user_id" => $v,
                "create_time" => strtotime(date("Y-m-d 09:00:00", time())),
                "update_time" => strtotime(date("Y-m-d 09:00:00", time()))
            ];
            $data1[] = $item;
        }
        (new AttendanceMapper())->addAll($data);
        (new PartTimeMapper())->addAll($data1);
        # 查询昨日未签退用户记录半勤
        $noCheckOut = $this->columnBy([["create_time", "=", strtotime(date("Y-m-d 9:00:00", strtotime("-1 day")))], ["check_out_time", "=", 0], ["result", ">", 0.5]], "id");
        $this->updateWhere(["id" => $noCheckOut], ["result" => 0.5]);
    }
}
