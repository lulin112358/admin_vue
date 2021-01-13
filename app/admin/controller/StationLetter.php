<?php


namespace app\admin\controller;


use app\admin\service\EngineerErrService;
use app\Code;

class StationLetter extends Base
{
    /**
     * 获取待处理
     * @param EngineerErrService $service
     */
    public function waitDeal(EngineerErrService $service) {
        try {
            $data = $service->errOrders(0);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 处理报错
     * @param EngineerErrService $service
     */
    public function dealErr(EngineerErrService $service) {
        $param = input("param.");
        try {
            $res = $service->dealErr($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败 请重试");
        $this->ajaxReturn("操作成功");
    }

    /**
     * 获取已读报错订单
     * @param EngineerErrService $service
     */
    public function alreadyRead(EngineerErrService $service) {
        try {
            $data = $service->alreadyRead();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取已处理订单
     * @param EngineerErrService $service
     */
    public function alreadyDeal(EngineerErrService $service) {
        try {
            $data = $service->errOrders(1);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取站内信
     * @param EngineerErrService $service
     */
    public function stationLetters(EngineerErrService $service) {
        try {
            $data = $service->stationLetters();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
