<?php


namespace app\admin\controller;


use app\admin\service\SchoolService;
use app\Code;

class School extends Base
{
    /**
     * 学校列表
     *
     * @param SchoolService $service
     */
    public function schools(SchoolService $service) {
        try {
            $list = $service->schools();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
