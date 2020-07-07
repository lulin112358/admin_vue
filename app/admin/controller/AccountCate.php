<?php


namespace app\admin\controller;


use app\admin\service\AccountCateService;
use app\Code;
use app\validate\AccountCateValidate;

class AccountCate extends Base
{
    /**
     * 列表
     * @param AccountCateService $service
     */
    public function cateList(AccountCateService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($list);
    }

    /**
     * 添加
     * @param AccountCateService $service
     * @param AccountCateValidate $validate
     */
    public function addCate(AccountCateService $service, AccountCateValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->add($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");

        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }

    /**
     * 查询指定
     * @param AccountCateService $service
     * @param AccountCateValidate $validate
     */
    public function cateOne(AccountCateService $service, AccountCateValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("one")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $data = $service->one(["id" => $param["id"]], "id, cate_name");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$data)
            $this->ajaxReturn(Code::ERROR, "暂无数据");

        $this->ajaxReturn($data);
    }

    /**
     * 更新
     * @param AccountCateService $service
     * @param AccountCateValidate $validate
     */
    public function updateCate(AccountCateService $service, AccountCateValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->update($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn(Code::SUCCESS, "更新成功");
    }

    /**
     * 删除
     * @param AccountCateService $service
     * @param AccountCateValidate $validate
     */
    public function delCate(AccountCateService $service, AccountCateValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->del($param["id"]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn(Code::SUCCESS, "删除成功");
    }
}