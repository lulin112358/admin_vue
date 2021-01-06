<?php


namespace app\engineer\controller;


use app\Code;
use app\engineer\service\ManuscriptFeeService;

class Manuscript extends Base
{
    /**
     * 写手稿费
     * @param ManuscriptFeeService $service
     */
    public function manuscriptFee(ManuscriptFeeService $service) {
        try {
            $list = $service->manuscriptFee();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
