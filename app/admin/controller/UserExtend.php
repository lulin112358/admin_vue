<?php


namespace app\admin\controller;


use app\admin\service\UserExtendService;
use app\Code;
use app\validate\UserExtendValidate;

class UserExtend extends Base
{
    /**
     * 获取用户扩展信息
     * @param UserExtendService $service
     * @param UserExtendValidate $validate
     */
    public function userExtendInfo(UserExtendService $service, UserExtendValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("user_extend")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->userExtendInfo($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 更新用户扩展信息
     * @param UserExtendService $service
     * @param UserExtendValidate $validate
     */
    public function updateUserExtendInfo(UserExtendService $service, UserExtendValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateUserExtendInfo($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }
}
