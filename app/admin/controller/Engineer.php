<?php


namespace app\admin\controller;


use app\admin\service\EngineerService;
use app\Code;
use app\validate\EngineerValidate;

class Engineer extends Base
{
    public function engineerBaseInfo(EngineerService $service) {
        try {
            $data = $service->engineersBaseInfo();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 工程师列表
     *
     * @param EngineerService $service
     */
    public function engineer(EngineerService $service) {
        $param = input("param.");
        try {
            $list = $service->engineer($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

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


    /**
     * 删除工程师
     *
     * @param EngineerService $service
     * @param EngineerValidate $validate
     */
    public function delEngineer(EngineerService $service, EngineerValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateWhere(["id" => $param["id"]], ["is_delete" => 1]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn("删除成功");
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

    /**
     * 工程师导出
     * @param EngineerService $service
     */
    public function export(EngineerService $service) {
        $param = input("param.");
        try {
            $service->export($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
    }
}
