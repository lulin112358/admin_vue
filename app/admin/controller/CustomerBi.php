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

    public function customerBiAmount(CustomerBiService $service) {
        $param = input("param.");
        try {
            $list = $service->customerBiAmount($param);
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
        try {
            $list = $service->accountColSort();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
