<?php


namespace app\admin\controller;


use app\admin\service\OriginService;
use app\Code;

class Origin extends Base
{
    public function addOrigin(OriginService $service) {
        $params = input("param.");
        try {
            $res = $service->addOrigin($params);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");

        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }

    /**
     * 获取所有来源
     * @param OriginService $service
     */
    public function allOrigin(OriginService $service) {
        try {
            $origin = $service->all("id, origin_name");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($origin);
    }
}
