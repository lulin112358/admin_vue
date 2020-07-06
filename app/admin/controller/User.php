<?php


namespace app\admin\controller;


use app\admin\service\UserRoleService;
use app\Code;
use app\validate\UserRoleValidate;

class User extends Base
{
    /**
     * 获取用户/角色关联列表
     * @param UserRoleService $service
     */
    public function userList(UserRoleService $service) {
        try {
            $data = $service->getList();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$data)
            $this->ajaxReturn(Code::ERROR, "暂无数据");

        $this->ajaxReturn($data);
    }

    /**
     * 获取用户/角色信息
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function getUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("getOne")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $data = $service->getOne($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$data)
            $this->ajaxReturn(Code::ERROR, "暂无数据");

        $this->ajaxReturn($data);
    }

    /**
     * 添加用户
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function addUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->addUser($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");

        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }

    /**
     * 更新用户
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function updateUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateUser($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn(Code::SUCCESS, "更新成功");
    }

    /**
     * 删除用户
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function delUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->delData($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn(Code::SUCCESS, "删除成功");
    }

    /**
     * 更新用户状态
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function updateStatus(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("upStatus")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateStatus($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn(Code::SUCCESS, "操作成功");
    }
}