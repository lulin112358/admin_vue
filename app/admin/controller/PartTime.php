<?php


namespace app\admin\controller;


use app\admin\service\PartTimeService;
use app\Code;
use app\validate\PartTimeValidate;

class PartTime extends Base
{
    /**
     * 兼职数据
     * @param PartTimeService $service
     */
    public function partTimes(PartTimeService $service) {
        $param = input("param.");
        try {
            $data = $service->partTimes($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 单行兼职数据
     * @param PartTimeService $service
     */
    public function partTimeRow(PartTimeService $service) {
        $param = input("param.");
        try {
            $data = $service->partTimes($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 更新薪水
     * @param PartTimeService $service
     */
    public function updateSalary(PartTimeService $service, PartTimeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("updateSalary")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateSalary($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");
        $this->ajaxReturn("更新成功");
    }

    /**
     * 兼职详情
     * @param PartTimeService $service
     */
    public function partTimeDetail(PartTimeService $service, PartTimeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("detail")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->partTimeDetail($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 更新兼职信息
     * @param PartTimeService $service
     * @param PartTimeValidate $validate
     */
    public function updatePartTime(PartTimeService $service, PartTimeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("updatePartTime")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updatePartTime($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");
        $this->ajaxReturn("更新成功");
    }
}
