<?php
declare (strict_types = 1);

namespace app\engineer\controller;

use app\Code;
use app\engineer\service\UserEngineerService;
use app\validate\UserEngineerValidate;
use jwt\Jwt;

class Index extends Base
{
    /**
     * 工程师登录
     * @param UserEngineerService $service
     * @param UserEngineerValidate $validate
     */
    public function login(UserEngineerService $service, UserEngineerValidate $validate)
    {
        $param = input("param.");
        if (!$validate->scene("login")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $exits = $service->login($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($exits !== false) {
            $data = [
                'uid' => $exits['engineer_id']
            ];
            $jwt = Jwt::generateToken($data);
            $this->ajaxReturn(Code::SUCCESS, "登录成功", ["name" => $exits["qq_nickname"]], $jwt);
        }
        $this->ajaxReturn(Code::ERROR, "账号或密码错误");
    }

    /**
     * 修改密码
     * @param UserEngineerService $service
     * @param UserEngineerValidate $validate
     */
    public function updatePassword(UserEngineerService $service, UserEngineerValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("updatePassword")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updatePwd($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }
}
