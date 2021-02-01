<?php


namespace app\customer\controller;


use app\Code;
use app\customer\service\EngineerService;
use app\customer\validate\EngineerValidate;

class Engineer extends Base
{
    /**
     * 添加工程师
     * @param EngineerService $service
     * @param EngineerValidate $validate
     */
    public function addEngineer(EngineerService $service, EngineerValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $param["create_time"] = time();
            $res = $service->addEngineer($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败 请检查QQ昵称是否包含特殊表情 如包含请删除后重试");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 更新工程师收款码
     * @param EngineerService $service
     * @param EngineerValidate $validate
     */
    public function updateEngineer(EngineerService $service, EngineerValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateEngineer($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "提交失败");
        $this->ajaxReturn("提交成功");
    }
}
