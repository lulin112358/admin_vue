<?php


namespace app\admin\controller;


use app\admin\service\CollectCodeUserService;
use app\Code;

class CollectCodeUser extends Base
{
    /**
     * 获取权限列表
     * @param CollectCodeUserService $service
     */
    public function collectCodeUser(CollectCodeUserService $service) {
        $param = input("param.");
        try {
            $data = $service->collectCodeUser($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 分配权限
     * @param CollectCodeUserService $service
     */
    public function assignAuth(CollectCodeUserService $service) {
        $param = input("param.");
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
