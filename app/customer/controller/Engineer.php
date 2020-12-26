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
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }
}
