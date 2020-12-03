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
        $pid = input("param.pid", 0);
        try {
            $list = $service->professions(["pid" => $pid]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
