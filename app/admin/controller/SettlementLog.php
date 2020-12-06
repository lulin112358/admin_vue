<?php


namespace app\admin\controller;


use app\admin\service\SettlementLogService;
use app\Code;

class SettlementLog extends Base
{
    /**
     * 获取全部结算记录
     *
     * @param SettlementLogService $service
     */
    public function settlementLogs(SettlementLogService $service) {
        $param = input("param.");
        try {
            $list = $service->settlementLogs($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 结算记录
     * @param SettlementLogService $service
     */
    public function export(SettlementLogService $service) {
        $param = input("param.");
        try {
            $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
