<?php


namespace app\automation\controller;


use app\automation\service\UserEngineersService;
use app\Code;

class Login extends Base
{
    /**
     * 工程师登录
     * @param UserEngineersService $service
     */
    public function login(UserEngineersService $service) {
        $param = input("param.");
        try {
            $res = $service->login($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "账户或密码错误");
        $this->ajaxReturn(Code::SUCCESS, "success", $res["engineer_id"]);
    }
}
