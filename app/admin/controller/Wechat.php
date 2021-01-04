<?php


namespace app\admin\controller;


use app\admin\service\WechatService;
use app\Code;
use app\validate\WechatValidate;

class Wechat extends Base
{
    /**
     * 获取所有沉淀微信
     *
     * @param WechatService $service
     */
    public function wechat(WechatService $service) {
        try {
            $data = $service->wechats();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 沉淀微信排序列表
     * @param WechatService $service
     */
    public function wechatSort(WechatService $service) {
        try {
            $data = $service->wechatSort();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取沉淀微信信息
     *
     * @param WechatService $service
     * @param WechatValidate $validate
     */
    public function wechatInfo(WechatService $service, WechatValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->findBy(["id" => $param["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加沉淀微信
     *
     * @param WechatService $service
     * @param WechatValidate $validate
     */
    public function addWechat(WechatService $service, WechatValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->addWechat($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 沉淀微信
     *
     * @param WechatService $service
     * @param WechatValidate $validate
     */
    public function updateWechat(WechatService $service, WechatValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateWechat($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        $this->ajaxReturn("修改成功");
    }


    /**
     * 删除沉淀微信
     *
     * @param WechatService $service
     * @param WechatValidate $validate
     */
    public function delWechat(WechatService $service, WechatValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateWhere($param, ["status" => 0]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");
        $this->ajaxReturn("删除成功");
    }
}
