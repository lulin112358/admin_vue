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

    /**
     * 获取收款记录
     * @param OrdersDepositService $service
     * @param OrdersDepositValidate $validate
     */
    public function orderPaymentLog(OrdersDepositService $service, OrdersDepositValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("list")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->paymentLog($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 修改收款账号
     * @param OrdersDepositService $service
     * @param OrdersDepositValidate $validate
     */
    public function updateDepositAccount(OrdersDepositService $service, OrdersDepositValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update_account")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateDepositAccount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }

    /**
     * 获取用户今日对账信息
     * @param OrdersDepositService $service
     */
    public function userPaymentLogByDay(OrdersDepositService $service) {
        try {
            $data = $service->userPaymentLogByDay();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
