<?php


namespace app\admin\service;


use app\mapper\AttendanceMapper;
use app\mapper\UserExtendMapper;
use Carbon\Carbon;

class UserExtendService extends BaseService
{
    protected $mapper = UserExtendMapper::class;

    /**
     * 获取用户扩展信息
     * @param $param
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userExtendInfo($param) {
        // 设置中文
        Carbon::setLocale("zh");
        $carbon = new Carbon();
        $where = [
            "u.id" => $param["user_id"]
        ];
        # 获取用户所有考勤记录
        $attendance = (new AttendanceMapper())->columnBy(["user_id" => $param["user_id"]], "result");
        $data = (new UserExtendMapper())->userExtendInfo($where);
        $time = Carbon::parse(date("Y-m-d H:i:s", $data["entry_time"] == 0 ? $data["tmp_entry_time"] : $data["entry_time"]));
        $data["status"] = $data["status"] == 1 ? "在职" : "离职";
        $data["school_id"] = $data["school_name"];
        $data["attendance_rate"] = count($attendance)==0?"0%":(floatval(round(array_sum($attendance)/count($attendance), 2))*100)."%";
        $part = 1;
        $diffYear = $carbon->diffInYears($time);
        $diffMonth = $carbon->diffInMonths($time);
        if ($diffYear > 0) {
            $part = 4;
        }
        if ($diffYear <= 0 && $diffMonth > 0) {
            $part = 2;
        }
        $data["entry_days"] = (new Carbon())->diffForHumans($time, true, false, $part);
        $data["entry_time"] = $data["entry_time"] == 0 ? date("Y-m-d", $data["tmp_entry_time"]) : date("Y-m-d", $data["entry_time"]);
        return $data;
    }

    /**
     * 更新用户扩展信息
     * @param $param
     * @return bool
     */
    public function updateUserExtendInfo($param) {
        $exits = $this->findBy(["user_id" => $param["user_id"]]);
        if ($exits) {
            $data = [
                "age" => $param["age"],
                "actual_age" => $param["actual_age"],
                "sex" => $param["sex"],
                "id_card" => $param["id_card"],
                "entry_time" => strtotime($param["entry_time"]),
                "degree_id" => $param["degree_id"],
                "hometown" => $param["hometown"],
                "current_residence" => $param["current_residence"],
                "update_time" => time()
            ];
            if (!is_string($param["school_id"])) {
                $data["school_id"] = $param["school_id"];
            }
            $res = $this->updateWhere(["user_id" => $param["user_id"]], $data);
            if ($res === false)
                return false;
        }else {
            $data = [
                "user_id" => $param["user_id"],
                "age" => $param["age"],
                "actual_age" => $param["actual_age"],
                "sex" => $param["sex"],
                "id_card" => $param["id_card"],
                "entry_time" => strtotime($param["entry_time"]),
                "degree_id" => $param["degree_id"],
                "hometown" => $param["hometown"],
                "current_residence" => $param["current_residence"],
                "school_id" => $param["school_id"]??0,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($data);
            if (!$res)
                return false;
        }
        return true;
    }
}
