<?php


namespace app\admin\controller;


use app\admin\service\RoleAuthRowService;
use app\Code;
use app\validate\RoleAuthRowValidate;

class RoleAuthRow extends Base
{
    /**
     * 获取行权限信息
     *
     * @param RoleAuthRowService $service
     * @param RoleAuthRowValidate $validate
     */
    public function roleAuthRowInfo(RoleAuthRowService $service, RoleAuthRowValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $info = $service->roleAuthRowInfo($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }


    /**
     * 绑定行权限
     *
     * @param RoleAuthRowService $service
     * @param RoleAuthRowValidate $validate
     */
    public function assignRoleAuthRow(RoleAuthRowService $service, RoleAuthRowValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("assign")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->assignRoleAuthRow($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn("操作成功");
    }
}
