<?php


namespace app\admin\controller;


use app\admin\service\RefundLogService;
use app\admin\service\RefundService;
use app\Code;
use app\validate\RefundValidate;

class Refund extends Base
{
    /**
     * 退款
     * @param RefundService $service
     */
    public function refund(RefundService $service, RefundValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->refund($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }

    /**
     * 退款列表
     * @param RefundService $service
     */
    public function refundList(RefundService $service) {
        $param = input("param.");
        try {
            $list = $service->refundList($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 退款操作
     * @param RefundService $service
     * @param RefundValidate $validate
     */
    public function refundHandle(RefundService $service, RefundValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("refund")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->refundHandle($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        $this->ajaxReturn("操作成功");
    }

    /**
     * 退款记录
     * @param RefundLogService $service
     */
    public function refundLogList(RefundLogService $service) {
        $param = input("param.");
        try {
            $list = $service->refundLogList($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 导出退款列表
     * @param RefundService $service
     */
    public function exportRefund(RefundService $service) {
        $param = input("param.");
        try {
            $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 导出退款记录
     * @param RefundLogService $service
     */
    public function exportRefundLog(RefundLogService $service) {
        $param = input("param.");
        try {
            $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }


    /**
     * 驳回
     * @param RefundService $service
     */
    public function turnDown(RefundService $service, RefundValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("turnDown")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->turnDown($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }

    /**
     * 被驳回退款列表
     * @param RefundService $service
     */
    public function turnDownList(RefundService $service) {
        try {
            $data = $service->turnDownList();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 修改退款信息
     * @param RefundService $service
     */
    public function updateRefund(RefundService $service) {
        $param = input("param.");
        try {
            $res = $service->updateRefund($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }

    /**
     * 驳回记录
     * @param RefundService $service
     */
    public function turnDownLog(RefundService $service) {
        $param = input("param.");
        try {
            $data = $service->turnDownLog($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
