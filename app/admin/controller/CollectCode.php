<?php


namespace app\admin\controller;


use app\admin\service\CollectCodeService;
use app\Code;
use app\validate\CollectCodeValidate;

class CollectCode extends Base
{
    /**
     * 收款码列表
     * @param CollectCodeService $service
     */
    public function collectCode(CollectCodeService $service) {
        try {
            $data = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取收款码信息
     * @param CollectCodeService $service
     */
    public function collectCodeInfo(CollectCodeService $service) {
        $param = input("param.");
        try {
            $data = $service->findBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加收款码
     * @param CollectCodeService $service
     */
    public function addCollectCode(CollectCodeService $service, CollectCodeValidate $validate) {
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
     * 更新收款码
     * @param CollectCodeService $service
     */
    public function updateCollectCode(CollectCodeService $service, CollectCodeValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
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
     * 删除收款码
     * @param CollectCodeService $service
     */
    public function delCollectCode(CollectCodeService $service, CollectCodeValidate $validate) {
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

    /**
     * 获取用户权限收款码
     * @param CollectCodeService $service
     */
    public function userCollectCode(CollectCodeService $service) {
        try {
            $data = $service->userCollectCode();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
