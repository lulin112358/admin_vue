<?php


namespace app\admin\controller;


use app\admin\service\MarketBiService;
use app\Code;

class MarketBi extends Base
{
    /**
     * 市场人员BI统计数据
     * @param MarketBiService $service
     */
    public function marketUserBi(MarketBiService $service) {
        $param = input("param.");
        try {
            $list = $service->marketUserBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 市场人员详情来源BI数据
     * @param MarketBiService $service
     */
    public function marketUserOriginBi(MarketBiService $service) {
        $param = input("param.");
        try {
            $list = $service->marketUserOriginBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
