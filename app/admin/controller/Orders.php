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
        "contact_qq",
        "cate_name"
    ];
    /**
     * 获取所有订单
     *
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
     * 内部编辑订单
     * @param OrdersService $service
     */
    public function innerEngineerOrders(OrdersService $service) {
        $param = input("param.");
        try {
            $data = $service->innerEngineerOrders($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    /**
     * 获取指定订单记录
     *
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function order(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("one")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->order($param);
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

        $this->ajaxReturn(Code::SUCCESS, "添加成功", $res);
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
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");

        $this->ajaxReturn("修改成功");
    }


    /**
     * 获取添加订单自动填充数据
     * @param OrdersService $service
     */
    public function ordersAutoFill(OrdersService $service) {
        $param = input("param.");
        try {
            $data = $service->autoFill($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    /**
     * 删除订单
     *
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function delOrder(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->deleteOrder(["id" => $param["order_id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        $this->ajaxReturn("删除成功");
    }

    /**
     * 删除订单工程师
     *
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function delEngineer(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->deleteEngineer($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");
        $this->ajaxReturn("删除成功");
    }


    /**
     * 分单
     *
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function splitOrder(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("split")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->splitOrder($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn("操作成功");
    }

    /**
     * 确认信息
     * @param OrdersService $service
     */
    public function confirmInfo(OrdersService $service) {
        $param = input("param.");
        try {
            $data = $service->confirmInfo($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 上传文档
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function bindDoc(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("bindDoc")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->bindDoc($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "上传失败");
        $this->ajaxReturn("上传成功");
    }

    /**
     * 下载列表
     * @param OrdersService $service
     * @param OrdersValidate $validate
     */
    public function docList(OrdersService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("docList")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->docList($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
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


    /**
     * 导出订单数据
     *
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
     * 导出内部编辑订单数据
     *
     * @param OrdersService $service
     */
    public function innerEngineerExport(OrdersService $service) {
        $param = input("param.");
        try {
            $service->innerEngineerExport($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }


    /**
     * 获取之前添加的前10学校列表
     * @param OrdersService $service
     */
    public function topSchool(OrdersService $service) {
        try {
            $data = $service->topSchools();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 待审核订单
     * @param OrdersService $service
     */
    public function reviewOrders(OrdersService $service) {
        $param = input("param.");
        try {
            $data = $service->reviewOrders($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    public function canProvide(OrdersService $service) {
        $param = input("param.");
        try {
            $res = $service->canProvide($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }
}
