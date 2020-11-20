<?php


namespace app\admin\controller;


use app\admin\service\EngineerService;
use app\Code;
use app\validate\EngineerValidate;

class Engineer extends Base
{
    /**
     * 工程师列表
     *
     * @param EngineerService $service
     */
    public function engineer(EngineerService $service) {
        try {
            $list = $service->engineer();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }


    /**
     * 更新工程师状态
     *
     * @param EngineerService $service
     * @param EngineerValidate $validate
     */
    public function updateEngineer(EngineerService $service, EngineerValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("updateStatus")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateBy($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn("更新成功");
    }
}
