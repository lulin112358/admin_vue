<?php


namespace app\admin\controller;


use app\admin\service\AttendanceGroupService;
use app\Code;

class AttendanceGroup extends Base
{
    /**
     * 获取所有考勤组
     * @param AttendanceGroupService $service
     */
    public function attendanceGroups(AttendanceGroupService $service) {
        try {
            $data = $service->all();
            foreach ($data as $k => $v) {
                $data[$k]["work_time"] = $v["start_time"].'~'.$v["end_time"];
            }
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    /**
     * 添加考勤组
     * @param AttendanceGroupService $service
     */
    public function addAttendanceGroup(AttendanceGroupService $service) {
        $param = input("param.");
        try {
            $res = $service->addAttendanceGroup($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 更新考勤组
     * @param AttendanceGroupService $service
     */
    public function updateAttendanceGroup(AttendanceGroupService $service) {
        $param = input("param.");
        try {
            $res = $service->updateAttendanceGroup($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");
        $this->ajaxReturn("更新成功");
    }

    /**
     * 删除考勤组
     * @param AttendanceGroupService $service
     */
    public function delAttendanceGroup(AttendanceGroupService $service) {
        $param = input("param.");
        try {
            $res = $service->deleteBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");
        $this->ajaxReturn("删除成功");
    }

    /**
     * 获取考勤组信息
     * @param AttendanceGroupService $service
     */
    public function attendanceGroupInfo(AttendanceGroupService $service) {
        $param = input("param.");
        try {
            $data = $service->findBy(["id" => $param["id"]]);
            if ($data) {
                $data["work_time"] = [$data["start_time"], $data["end_time"]];
            }
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
