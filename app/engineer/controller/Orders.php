<?php


namespace app\engineer\controller;


use app\Code;
use app\engineer\service\OrdersService;
use app\validate\OrdersValidate;

class Orders extends Base
{
    /**
     * 获取写手订单
     * @param OrdersService $service
     */
    public function orders(OrdersService $service) {
        $param = input("param.");
        try {
            $data = $service->orders($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 导出
     * @param OrdersService $service
     */
    public function export(OrdersService $service) {
        $param = input("param.");
        try {
           $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 下载文档
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function downDoc(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("downDoc")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $service->downDoc($param);
        }catch (\Exception $exception) {
            exit($exception->getMessage());
//            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
