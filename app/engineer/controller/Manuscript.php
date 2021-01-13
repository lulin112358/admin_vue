<?php


namespace app\engineer\controller;


use app\Code;
use app\engineer\service\ManuscriptFeeService;
use app\validate\EngineerErrValidate;
use app\validate\OrdersValidate;

class Manuscript extends Base
{
    /**
     * 写手稿费
     * @param ManuscriptFeeService $service
     */
    public function manuscriptFee(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $list = $service->manuscriptFee($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 确认核定
     * @param ManuscriptFeeService $service
     */
    public function confirmManuscript(ManuscriptFeeService $service, OrdersValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("confirmManuscript")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->confirmManuscript($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败 请重试");
        $this->ajaxReturn("操作成功");
    }

    /**
     * 结算记录
     * @param ManuscriptFeeService $service
     */
    public function settlementLog(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $data = $service->settlementLog($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 稿费报错
     * @param ManuscriptFeeService $service
     * @param EngineerErrValidate $validate
     */
    public function errSubmit(ManuscriptFeeService $service, EngineerErrValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("errSubmit")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->errSubmit($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "提交失败");
        $this->ajaxReturn("已提交管理员审核 请耐心等待");
    }

    /**
     * 稿费报错更新
     * @param ManuscriptFeeService $service
     * @param EngineerErrValidate $validate
     */
    public function errUpdate(ManuscriptFeeService $service, EngineerErrValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("errSubmit")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->errUpdate($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("已提交管理员审核 请耐心等待");
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
     * 导出结算记录
     * @param ManuscriptFeeService $service
     */
    public function exportLog(ManuscriptFeeService $service) {
        $param = input("param.");
        try {
            $service->exportLog($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
