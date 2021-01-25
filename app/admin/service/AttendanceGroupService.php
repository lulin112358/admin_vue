<?php


namespace app\admin\service;


use app\mapper\AttendanceGroupMapper;

class AttendanceGroupService extends BaseService
{
    protected $mapper = AttendanceGroupMapper::class;

    /**
     * 添加考勤组
     * @param $param
     * @return mixed
     */
    public function addAttendanceGroup($param) {
        $insData = [
            "name" => $param["name"],
            "start_time" => $param["work_time"][0],
            "end_time" => $param["work_time"][1],
            "create_time" => time(),
            "update_time" => time()
        ];
        return $this->add($insData);
    }

    /**
     * 更新考勤组
     * @param $param
     * @return mixed
     */
    public function updateAttendanceGroup($param) {
        if (isset($param["work_time"]) && !empty($param["work_time"])) {
            $param["start_time"] = $param["work_time"][0];
            $param["end_time"] = $param["work_time"][1];
            unset($param["work_time"]);
        }
        $param["update_time"] = time();
        return $this->updateBy($param);
    }
}
