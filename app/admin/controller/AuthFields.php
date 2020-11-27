<?php


namespace app\admin\controller;


use app\admin\service\AuthFieldsService;
use app\Code;
use app\validate\AuthFieldsValidate;

class AuthFields extends Base
{
    /**
     * 获取所有权限列数据
     *
     * @param AuthFieldsService $service
     */
    public function authFields(AuthFieldsService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取单个列信息
     *
     * @param AuthFieldsService $service
     * @param AuthFieldsValidate $validate
     */
    public function authFieldInfo(AuthFieldsService $service, AuthFieldsValidate $validate) {
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
     * 添加权限列
     *
     * @param AuthFieldsService $service
     * @param AuthFieldsValidate $validate
     */
    public function addAuthField(AuthFieldsService $service, AuthFieldsValidate $validate) {
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

        $this->ajaxReturn("添加成功");
    }

    /**
     * 修改权限列
     *
     * @param AuthFieldsService $service
     * @param AuthFieldsValidate $validate
     */
    public function updateAuthField(AuthFieldsService $service, AuthFieldsValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $param["update_time"] = time();
            $res = $service->updateBy($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");

        $this->ajaxReturn("修改成功");
    }

    /**
     * 删除权限列
     *
     * @param AuthFieldsService $service
     * @param AuthFieldsValidate $validate
     */
    public function delAuthField(AuthFieldsService $service, AuthFieldsValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->deleteBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn("删除成功");
    }

    /**
     * 获取所有orders的所有列
     * @param AuthFieldsService $service
     */
    public function fieldsList(AuthFieldsService $service) {
        try {
            $fields = $service->fields();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($fields);
    }
}
