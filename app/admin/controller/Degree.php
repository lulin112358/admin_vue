<?php


namespace app\admin\controller;


use app\admin\service\DegreeService;
use app\Code;
use app\validate\DegreeValidate;

class Degree extends Base
{
    /**
     * 获取所有学位
     *
     * @param DegreeService $service
     */
    public function degree(DegreeService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取学位信息
     *
     * @param DegreeService $service
     * @param DegreeValidate $validate
     */
    public function degreeInfo(DegreeService $service, DegreeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $info = $service->findBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($info);
    }

    /**
     * 添加学位
     *
     * @param DegreeService $service
     * @param DegreeValidate $validate
     */
    public function addDegree(DegreeService $service, DegreeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->add($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 修改学位
     *
     * @param DegreeService $service
     * @param DegreeValidate $validate
     */
    public function updateDegree(DegreeService $service, DegreeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $param["update_time"] = time();
            $res = $service->updateBy($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }

    /**
     * 删除学位
     *
     * @param DegreeService $service
     * @param DegreeValidate $validate
     */
    public function delDegree(DegreeService $service, DegreeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->deleteBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");
        $this->ajaxReturn("删除成功");
    }
}
