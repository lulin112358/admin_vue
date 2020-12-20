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

    public function originDetailBi(OriginBiService $service) {
        $param = input("param.");
        try {
            $list = $service->originDetailBi($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
