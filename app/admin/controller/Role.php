<?php


namespace app\admin\controller;


use app\admin\service\AuthService;
use app\admin\service\RoleService;
use app\Code;
use app\validate\AuthValidate;
use app\validate\RoleValidate;

class Role extends Base
{
    /**
     * 获取所有角色
     * @param RoleService $service
     */
    public function allRole(RoleService $service) {
        try {
            $data = $service->allRole();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取单个角色信息
     * @param RoleService $service
     * @param RoleValidate $validate
     */
    public function getRole(RoleService $service, RoleValidate $validate) {
        $param = input('param.');
        if (!$validate->scene("getOne")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $data = $service->getRole($param["id"]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$data)
            $this->ajaxReturn(Code::ERROR, "暂无数据");
        $this->ajaxReturn($data);
    }

    /**
     * 添加角色
     * @param RoleService $service
     * @param RoleValidate $validate
     */
    public function addRole(RoleService $service, RoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("addRole")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->addRole($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");

        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }

    /**
     * 更新角色
     * @param RoleService $service
     * @param RoleValidate $validate
     */
    public function updateRole(RoleService $service, RoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("updateRole")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateRole($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn(Code::SUCCESS, "更新成功");
    }

    /**
     * 删除角色
     * @param RoleService $service
     * @param RoleValidate $validate
     */
    public function delRole(RoleService $service, RoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("delRole")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->delRole($param["id"]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn(Code::SUCCESS, "删除成功");
    }

    /**
     * 更新数据
     * @param RoleService $service
     * @param RoleValidate $validate
     */
    public function updateData(RoleService $service, RoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateRole($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn(Code::SUCCESS, "操作成功");
    }

    /**
     * 权限分配
     * @param AuthService $service
     * @param AuthValidate $validate
     */
    public function assignAuth(AuthService $service, AuthValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("save")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->saveAuth($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn(Code::SUCCESS, "操作成功");
    }

    /**
     * 根据角色获取权限
     * @param AuthService $service
     * @param AuthValidate $validate
     */
    public function getRuleByRole(AuthService $service, AuthValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("getRule")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->getRuleByRole($param["role_id"]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($res);
    }
}