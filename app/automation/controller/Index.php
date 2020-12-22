<?php
declare (strict_types = 1);

namespace app\automation\controller;

use app\automation\service\OrderService;
use app\automation\service\UserService;
use app\Code;

class Index extends Base
{
    /**
     * 根据用户id获取用户信息
     * @param UserService $service
     */
    public function getUserInfo(UserService $service)
    {
        $param = input("param.");
        try {
            $info = $service->findBy(["id" => $param["user_id"]], "user_name, name");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }

    /**
     * 获取订单详细信息
     * @param OrderService $service
     */
    public function getOrderInfo(OrderService $service) {
        $param = input("param.");
        try {
            $info = $service->findBy(["order_sn" => $param["order_no"]], "require, delivery_time");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }
}
