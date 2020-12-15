<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\SoftwareService;

class Software extends Base
{
    /**
     * 获取软件列表
     *
     * @param SoftwareService $service
     */
    public function software(SoftwareService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
