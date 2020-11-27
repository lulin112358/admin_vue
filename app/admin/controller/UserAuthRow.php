<?php

namespace app\admin\controller;


use app\admin\service\UserAuthRowService;
use app\Code;
use app\validate\UserAuthRowValidate;

class UserAuthRow extends Base
{
    /**
     * 获取用户行权限
     * @param UserAuthRowService $service
     * @param UserAuthRowValidate $validate
     */
    public function userAuthRowInfo(UserAuthRowService $service, UserAuthRowValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->userAuthRow($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 绑定权限
     *
     * @param UserAuthRowService $service
     * @param UserAuthRowValidate $validate
     */
    public function assignUserAuthRow(UserAuthRowService $service, UserAuthRowValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("assign")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->assignAuth($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }
}
