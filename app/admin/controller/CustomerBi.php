<?php


namespace app\admin\controller;


use app\admin\service\CustomerBiService;
use app\Code;

class CustomerBi extends Base
{
    /**
     * 获取客服BI数据
     * @param CustomerBiService $service
     */
    public function customerBiCount(CustomerBiService $service) {
        $param = input("param.");
        try {
            $list = $service->customerBiCount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取客服BI账号列数据
     * @param CustomerBiService $service
     */
    public function customerBiCols(CustomerBiService $service) {
        $param = input("param.");
        try {
            $list = $service->accountColSort($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 客服接单BI数据
     * @param CustomerBiService $service
     */
    public function customerOrderBi(CustomerBiService $service) {
        $param = input("param.");
        try {
            $list = $service->customerOrderBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 客服接单业绩BI数据
     * @param CustomerBiService $service
     */
    public function cusOrderPerfBi(CustomerBiService $service) {
        $param = input("param.");
        try {
            $list = $service->cusOrderPerfBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 客服订单业绩详情BI数据
     * @param CustomerBiService $service
     */
    public function cusOrderPerfDetailBi(CustomerBiService $service) {
        $param = input("param.");
        try {
            $list = $service->cusOrderPerfDetailBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }


    /**
     * 导出
     * @param CustomerBiService $service
     */
    public function export(CustomerBiService $service) {
        $param = input("param.");
        try {
            $service->cusOrderPerExport($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
