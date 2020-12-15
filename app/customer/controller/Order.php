<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\OrderService;
use app\customer\validate\OrderValidate;

class Order extends Base
{
    /**
     * 获取订单信息
     * @param OrderService $service
     */
    public function orderInfo(OrderService $service) {
        $param = input("param.");
        try {
            $data = $service->orderInfo($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    /**
     * 客户确认订单更新订单信息
     * @param OrderService $service
     * @param OrderValidate $validate
     */
    public function updateOrder(OrderService $service, OrderValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateOrder($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "提交失败");
        $this->ajaxReturn("提交成功");
    }
}
