<?php


namespace app\automation\controller;


use app\automation\service\OrderService;
use app\Code;

class Order extends Base
{
    /**
     * 获取订单详细信息
     * @param OrderService $service
     */
    public function getOrderInfo(OrderService $service) {
        $param = input("param.");
        try {
            $info = $service->findBy(["order_sn" => $param["order_no"]], "require, delivery_time, manuscript_fee");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }

    /**
     * 自动分单
     * @param OrderService $service
     */
    public function splitOrder(OrderService $service) {
        $param = input("param.");
        try {
            $res = $service->splitOrder($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "分单失败");
        $this->ajaxReturn($res);
    }

    /**
     * 更新订单信息
     * @param OrderService $service
     */
    public function updateOrder(OrderService $service) {
        $param = input("param.");
        try {
            $res = $service->updateOrder($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");
        $this->ajaxReturn("更新成功");
    }

    /**
     * 通过订单编号获取主订单id
     * @param OrderService $service
     */
    public function getOrderIdByOrderSn(OrderService $service) {
        $param = input("param.");
        try{
            if (is_array($param["order_no"])) {
                $info = $service->selectBy(["order_sn" => $param["order_no"]], "main_order_id, order_sn");
            }else {
                $info = $service->findBy(["order_sn" => $param["order_no"]], "main_order_id");
            }
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }
}
