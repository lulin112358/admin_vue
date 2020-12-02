<?php


namespace app\admin\controller;


use app\admin\service\ProfessionService;
use app\Code;

class Profession extends Base
{
    /**
     * 获取专业列表
     *
     * @param ProfessionService $service
     */
    public function professions(ProfessionService $service) {
        try {
            $list = $service->all();
            $list = generateTree($list);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
