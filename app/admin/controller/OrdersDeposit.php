<?php


namespace app\admin\controller;


use app\admin\service\OrdersDepositService;
use app\Code;
use app\validate\OrdersDepositValidate;

class OrdersDeposit extends Base
{
    /**
     * 获取定金列表
     *
     * @param OrdersDepositService $service
     * @param OrdersDepositValidate $validate
     */
    public function deposit(OrdersDepositService $service, OrdersDepositValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("list")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->deposit($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
