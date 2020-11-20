<?php


namespace app\admin\controller;


use app\admin\service\OrdersFinalPaymentService;
use app\Code;
use app\validate\OrdersFinalPaymentValidate;

class OrdersFinalPayment extends Base
{

    /**
     * 获取尾款列表
     *
     * @param OrdersFinalPaymentService $service
     * @param OrdersFinalPaymentValidate $validate
     */
    public function finalPayment(OrdersFinalPaymentService $service, OrdersFinalPaymentValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("list")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->finalPayment($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
