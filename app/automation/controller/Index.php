<?php
declare (strict_types = 1);

namespace app\automation\controller;

use app\automation\service\EngineerService;
use app\automation\service\OrderService;
use app\automation\service\UserEngineersService;
use app\automation\service\UserService;
use app\Code;

class Index extends Base
{
    /**
     * 根据用户id获取用户信息
     * @param UserService $service
     */
    public function getUserInfo(UserService $service)
    {
        $param = input("param.");
        try {
            $info = $service->findBy(["id" => $param["user_id"]], "user_name, name");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }

    /**
     * 搜索工程师
     * @param EngineerService $service
     */
    public function searchEngineer(EngineerService $service) {
        $param = input("param.");
        try {
            $list = $service->selectBy([["contact_qq|contact_phone", "like", "%{$param['number']}%"]], "id, contact_qq as qq, contact_phone as phone, qq_nickname as nickname");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 更新密码
     * @param UserEngineersService $service
     */
    public function updatePassword(UserEngineersService $service) {
        $param = input("param.");
        try {
            $pwd = password_hash($param["password"], PASSWORD_DEFAULT);
            $res = $service->updateWhere(["engineer_id" => $param["uid"]], ["password" => $pwd]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }

    /**
     * 获取指定工程师稿费信息
     * @param OrderService $service
     */
    public function manuscript(OrderService $service) {
        $param = input("param.");
        try {
            $data = $service->manuscript($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 工程师稿费详情
     * @param OrderService $service
     */
    public function manuscriptDetail(OrderService $service) {
        $param = input("param.");
        try {
            $list = $service->manuscriptDetail($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
