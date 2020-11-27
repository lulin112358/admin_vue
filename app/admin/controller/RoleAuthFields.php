<?php


namespace app\admin\controller;


use app\admin\service\RoleAuthFieldsService;
use app\Code;
use app\validate\RoleAuthFieldsValidate;

class RoleAuthFields extends Base
{
    /**
     * 获取角色的权限
     *
     * @param RoleAuthFieldsService $service
     * @param RoleAuthFieldsValidate $validate
     */
    public function roleAuthFieldInfo(RoleAuthFieldsService $service, RoleAuthFieldsValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $info = $service->columnBy(["role_id" => $param["role_id"]], "field_id");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }

    /**
     * 分配列权限
     *
     * @param RoleAuthFieldsService $service
     * @param RoleAuthFieldsValidate $validate
     */
    public function assignRoleAuthField(RoleAuthFieldsService $service, RoleAuthFieldsValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("assign")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->assignRoleAuthField($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");

        $this->ajaxReturn("添加成功");
    }
}
