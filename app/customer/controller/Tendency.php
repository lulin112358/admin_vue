<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\TendencyService;

class Tendency extends Base
{
    /**
     * 倾向类型列表
     *
     * @param TendencyService $service
     */
    public function tendency(TendencyService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
