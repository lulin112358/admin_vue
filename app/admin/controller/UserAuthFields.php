<?php


namespace app\admin\controller;


use app\admin\service\UserAuthFieldsService;
use app\Code;
use app\validate\UserAuthFieldsValidate;

class UserAuthFields extends Base
{
    /**
     * 获取该用户权限列
     * @param UserAuthFieldsService $service
     */
    public function userAuthFields(UserAuthFieldsService $service, UserAuthFieldsValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("list")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->userAuthFields($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    public function assignUserAuthFields(UserAuthFieldsService $service, UserAuthFieldsValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("assign")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->assignFields($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn("操作成功");
    }
}
