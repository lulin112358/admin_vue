<?php


namespace app\admin\controller;


use app\admin\service\OrdersService;
use app\Code;
use app\validate\OrdersValidate;

class Orders extends Base
{
    /**
     * 允许修改的字段
     * @var string[]
     */
    private $field = [
        "total_amount",
        "customer_contact",
        "check_fee",
        "manuscript_fee",
        "note",
        "require",
        "status",
        "customer_manager",
        "biller",
        "wechat",
        "origin_name",
        "account",
        "delivery_time",
        "deposit",
        "final_payment",
        "contact_qq"
    ];
    /**
     * 获取所有订单
     *
     * @param OrdersService $service
     */
    public function orders(OrdersService $service) {
        try {
            $data = $service->orders();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加订单
     *
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function addOrder(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->addOrder($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);

        $this->ajaxReturn("添加成功");
    }


    /**
     * 修改订单信息
     *
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function updateOrder(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        if (!in_array($param["field"], $this->field))
            $this->ajaxReturn(Code::ERROR, "该字段不允许修改");
        try {
            $res = $service->updateOrder($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");

        $this->ajaxReturn("修改成功");
    }
}
