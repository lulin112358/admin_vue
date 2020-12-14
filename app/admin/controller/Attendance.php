<?php


namespace app\admin\controller;


use app\admin\service\AttendanceService;
use app\Code;
use app\validate\AttendanceValidate;

class Attendance extends Base
{
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
}
