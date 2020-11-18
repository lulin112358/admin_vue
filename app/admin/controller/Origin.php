<?php


namespace app\admin\controller;


use app\admin\service\OriginService;
use app\Code;
use app\validate\OriginValidate;

class Origin extends Base
{
    /**
     * 添加来源
     *
     * @param OriginService $service
     * @param OriginValidate $validate
     */
    public function addOrigin(OriginService $service, OriginValidate $validate) {
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
        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }


    /**
     * 来源列表
     *
     * @param OriginService $service
     */
    public function originList(OriginService $service) {
        try {
            $list = $service->originList();
        } catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }


    /**
     * 来源数据
     *
     * @param OriginService $service
     * @param OriginValidate $validate
     */
    public function origin(OriginService $service, OriginValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->findBy(["id" => $param["origin_id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    /**
     * 更新来源
     *
     * @param OriginService $service
     * @param OriginValidate $validate
     */
    public function updateOrigin(OriginService $service, OriginValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateOrigin($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        $this->ajaxReturn(Code::SUCCESS, "更新成功");
    }

    /**
     * 删除来源
     *
     * @param OriginService $service
     * @param OriginValidate $validate
     */
    public function delOrigin(OriginService $service, OriginValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateWhere(["id" => $param["origin_id"]], ["status" => 0]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");
        $this->ajaxReturn(Code::SUCCESS, "删除成功");
    }

    /**
     * 获取所有来源
     * @param OriginService $service
     */
//    public function allOrigin(OriginService $service) {
//        try {
//            $origin = $service->all("id, origin_name");
//        }catch (\Exception $exception) {
//            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
//        }
//        $this->ajaxReturn($origin);
//    }

    /**
     * 市场来源信息
     *
     * @param OriginService $service
     * @param OriginValidate $validate
     */
//    public function originInfo(OriginService $service, OriginValidate $validate) {
//        $param = input("param.");
//        if (!$validate->scene("info")->check($param))
//            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
//        try {
//            $data = $service->originInfo($param);
//        }catch (\Exception $exception) {
//            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
//        }
//        $this->ajaxReturn($data);
//    }


    /**
     * 添加来源
     * @param OriginService $service
     */
//    public function addOrigin(OriginService $service) {
//        $params = input("param.");
//        try {
//            $res = $service->addOrigin($params);
//        }catch (\Exception $exception) {
//            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
//        }
//        if (is_string($res))
//            $this->ajaxReturn(Code::ERROR, $res);
//
//        $this->ajaxReturn(Code::SUCCESS, "添加成功");
//    }

    /**
     * 来源列表
     * @param OriginService $service
     */
//    public function originList(OriginService $service) {
//        try {
//            $list = $service->originList();
//        }catch (\Exception $exception) {
//            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
//        }
//        $this->ajaxReturn($list);
//    }

}
