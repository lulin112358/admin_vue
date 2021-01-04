<?php


namespace app\admin\controller;


use app\admin\service\AmountAccountService;
use app\Code;
use app\validate\AmountAccountValidate;

class AmountAccount extends Base
{
    /**
     * 获取所有收款账户
     *
     * @param AmountAccountService $service
     */
    public function account(AmountAccountService $service) {
        try {
            $data = $service->selectBy(["status" => 1], "id, account, create_time, update_time", "id desc");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 收款账号排序列表
     * @param AmountAccountService $service
     */
    public function accountSort(AmountAccountService $service) {
        try {
            $data = $service->accountSort();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加收款账户
     *
     * @param AmountAccountService $service
     * @param AmountAccountValidate $validate
     */
    public function addAccount(AmountAccountService $service, AmountAccountValidate $validate) {
        $param =  input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->addAmountAccount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }


    /**
     * 获取收款账号信息
     *
     * @param AmountAccountService $service
     * @param AmountAccountValidate $validate
     */
    public function accountInfo(AmountAccountService $service, AmountAccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $info = $service->findBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }

    /**
     * 更新收款账户
     *
     * @param AmountAccountService $service
     * @param AmountAccountValidate $validate
     */
    public function updateAccount(AmountAccountService $service, AmountAccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateAccount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn("更新成功");
    }


    /**
     * 删除收款账号
     *
     * @param AmountAccountService $service
     * @param AmountAccountValidate $validate
     */
    public function delAccount(AmountAccountService $service, AmountAccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateWhere($param, ["status" => 0]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn("删除成功");
    }
}
