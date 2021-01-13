<?php


namespace app\admin\controller;


use app\admin\service\ManuscriptFeeService;
use app\admin\service\OrdersService;
use app\admin\service\SettlementLogService;
use app\Code;
use app\validate\ManuscriptFeeValidate;
use app\validate\OrdersValidate;

class ManuscriptFee extends Base
{
    /**
     * 获取工程师稿费结算请款
     *
     * @param OrdersService $service
     */
    public function manuscriptFees(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $list = $service->manuscriptFees($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取工程师稿费结算详情信息
     * @param OrdersService $service
     * @param ManuscriptFeeValidate $validate
     */
    public function engineerDetail(ManuscriptFeeService $service, ManuscriptFeeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("detail")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->engineerDetail($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取工程师已核对可结算稿费
     * @param ManuscriptFeeService $service
     */
    public function canSettlement(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $list = $service->manuscriptFees($param, true);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取工程师以核定可结算稿费详情
     * @param ManuscriptFeeService $service
     * @param ManuscriptFeeValidate $validate
     */
    public function canSettlementDetail(ManuscriptFeeService $service, ManuscriptFeeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("detail")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->engineerDetail($param, true);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 全部结算
     * @param SettlementLogService $service
     * @param OrdersValidate $validate
     */
    public function settlementAll(SettlementLogService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("settlement")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->settlementAll($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "结算失败");
        $this->ajaxReturn("结算成功");
    }

    /**
     * 外部直接结算
     * @param SettlementLogService $service
     * @param OrdersValidate $validate
     */
    public function directSettlement(SettlementLogService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("directSettlement")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->settlementAll($param, true);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "结算失败");
        $this->ajaxReturn("结算成功");
    }


    /**
     * 导出
     * @param ManuscriptFeeService $service
     */
    public function export(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 导出详情
     * @param ManuscriptFeeService $service
     */
    public function exportDetail(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $service->exportDetail($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 导出
     * @param ManuscriptFeeService $service
     */
    public function canSettlementExport(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $service->canSettlementExport($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }

    /**
     * 导出详情
     * @param ManuscriptFeeService $service
     */
    public function canSettlementDetailExport(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $service->canSettlementDetailExport($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
