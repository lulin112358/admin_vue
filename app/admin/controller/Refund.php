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
}
