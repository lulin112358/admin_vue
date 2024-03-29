<?php


namespace app\admin\controller;


use app\admin\service\AccountService;
use app\admin\service\OrdersAccountService;
use app\Code;
use app\validate\AccountValidate;
use app\validate\OrdersAccountValidate;
use think\facade\Db;

class Account extends Base
{
    /**
     * 获取所有接单账号列表
     *
     * @param AccountService $service
     */
    public function account(AccountService $service) {
        try {
            $data = $service->account();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 接单账号排序列表
     * @param AccountService $service
     */
    public function accountSort(AccountService $service) {
        try {
            $data = $service->accountSort();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取接单账号信息
     *
     * @param OrdersAccountService $service
     * @param OrdersAccountValidate $validate
     */
    public function accountInfo(AccountService $service, OrdersAccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->accountInfo($param["account_id"]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($res);
    }

    /**
     * 添加接单账号
     *
     * @param AccountService $service
     * @param AccountValidate $validate
     */
    public function addAccount(AccountService $service, AccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->addAccount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);

        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }


    /**
     * 修改接单账号
     *
     * @param AccountService $service
     * @param AccountValidate $validate
     */
    public function updateAccount(AccountService $service, AccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateAccount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        $this->ajaxReturn(Code::SUCCESS, "更新成功");
    }

    /**
     * 删除接单账号
     *
     * @param AccountService $service
     * @param AccountValidate $validate
     */
    public function delAccount(AccountService $service, AccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->delAccount($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        $this->ajaxReturn(Code::SUCCESS, "删除成功");
    }


    /**
     * 更新是否是沉淀微信
     * @param AccountService $service
     * @param AccountValidate $validate
     */
    public function updateIsWechat(AccountService $service, AccountValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update_is_wechat")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateIsWechat($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }
}
