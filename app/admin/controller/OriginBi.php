<?php


namespace app\admin\controller;


use app\admin\service\OriginBiService;
use app\Code;

class OriginBi extends Base
{
    /**
     * 来源BI统计数据
     * @param OriginBiService $service
     */
    public function originBi(OriginBiService $service) {
        $param = input("param.");
        try {
            $list = $service->originBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 来源详情
     * @param OriginBiService $service
     */
    public function originDetailBi(OriginBiService $service) {
        $param = input("param.");
        try {
            $list = $service->originDetailBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 对账信息
     * @param OriginBiService $service
     */
    public function originReconciliation(OriginBiService $service) {
        $param = input("param.");
        try {
            $list = $service->originReconciliation($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 导出
     * @param OriginBiService $service
     */
    public function export(OriginBiService $service) {
        $param = input("param.");
        try {
            $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 详情导出
     * @param OriginBiService $service
     */
    public function exportDetail(OriginBiService $service) {
        $param = input("param.");
        try {
            $service->exportDetail($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 对账详情导出
     * @param OriginBiService $service
     */
    public function exportRec(OriginBiService $service) {
        $param = input("param.");
        try {
            $service->exportRec($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
