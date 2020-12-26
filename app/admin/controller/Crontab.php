<?php


namespace app\admin\controller;


use app\admin\service\AttendanceService;
use app\Code;

/**
 * 定时任务
 * Class Crontab
 * @package app\admin\controller
 */
class Crontab extends Base
{
    /**
     * 添加考勤记录
     * @param AttendanceService $service
     */
    public function attendance(AttendanceService $service) {
        try {
            $service->addData();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
