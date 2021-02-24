<?php


namespace app\admin\controller;


use app\admin\service\VacationService;
use app\Code;

class Vacation extends Base
{
    /**
     * 添加/修改休假信息
     * @param VacationService $service
     */
    public function putVacation(VacationService $service) {
        $param = input("param.");
        try {
            $res = $service->putVacation($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }


    /**
     * vacations
     * @param VacationService $service
     */
    public function vacations(VacationService $service) {
        $param = input("param.");
        try {
            $data = $service->vacations($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 取消休假
     * @param VacationService $service
     */
    public function cancelVacation(VacationService $service) {
        $param = input("param.");
        try {
            $res = $service->cancelVacation($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }
}
