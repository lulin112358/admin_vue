<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\DegreeService;

class Degree extends Base
{
    public function degrees(DegreeService $service) {
        try {
            $list = $service->degrees();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
