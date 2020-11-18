<?php


namespace app\admin\controller;


use app\admin\service\EvaluationService;
use app\Code;
use app\validate\EvaluationValidate;

class Evaluation extends Base
{
    /**
     * 评价列表
     *
     * @param EvaluationService $service
     */
    public function evaluation(EvaluationService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }


    /**
     * 评价信息
     *
     * @param EvaluationService $service
     * @param EvaluationValidate $validate
     */
    public function evaluationInfo(EvaluationService $service, EvaluationValidate $validate) {
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
     * 添加评价
     *
     * @param EvaluationService $service
     * @param EvaluationValidate $validate
     */
    public function addEvaluation(EvaluationService $service, EvaluationValidate $validate) {
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
     * 修改评价
     *
     * @param EvaluationService $service
     * @param EvaluationValidate $validate
     */
    public function updateEvaluation(EvaluationService $service, EvaluationValidate $validate) {
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
     * 删除评价
     *
     * @param EvaluationService $service
     * @param EvaluationValidate $validate
     */
    public function delEvaluation(EvaluationService $service, EvaluationValidate $validate) {
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
