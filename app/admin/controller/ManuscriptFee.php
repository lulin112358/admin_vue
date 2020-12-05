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
        try {
            $list = $service->manuscriptFees();
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
}
