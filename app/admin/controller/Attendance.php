<?php


namespace app\admin\controller;


use app\admin\service\AttendanceService;
use app\Code;
use app\validate\AttendanceValidate;

class Attendance extends Base
{
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
     * @param AttendanceService $service
     */
    public function attendances(AttendanceService $service) {
        $param = input("param.");
        try {
            $list = $service->attendances($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取用户考勤信息
     * @param AttendanceService $service
     * @param AttendanceValidate $validate
     */
    public function userAttendances(AttendanceService $service, AttendanceValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("user_attendance")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->userAttendances($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 更新考勤信息
     * @param AttendanceService $service
     * @param AttendanceValidate $validate
     */
    public function updateAttendance(AttendanceService $service, AttendanceValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateAttendance($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");

        $this->ajaxReturn("修改成功");
    }

    /**
     * 获取考勤信息
     * @param AttendanceService $service
     * @param AttendanceValidate $validate
     */
    public function attendanceInfo(AttendanceService $service, AttendanceValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->findBy(["id" => $param["id"]]);
            $data["color"] = $this->color[$data["type"]];
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
