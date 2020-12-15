<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\ProfessionService;

class Profession extends Base
{
    /**
     * 获取专业数据
     * @param ProfessionService $service
     */
    public function professions(ProfessionService $service) {
        $param = input("param.");
        try {
            $list = $service->professions($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
