<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\SchoolService;

class School extends Base
{
    /**
     * 获取学校信息
     * @param SchoolService $service
     */
    public function schools(SchoolService $service) {
        $param = input("param.");
        try {
            $list = $service->schools($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
