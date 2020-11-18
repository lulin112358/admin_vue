<?php


namespace app\admin\controller;


use app\admin\service\GroupChatService;
use app\Code;
use app\validate\GroupChatValidate;

class GroupChat extends Base
{
    /**
     * 获取所有群列表
     * @param GroupChatService $service
     */
    public function groupChat(GroupChatService $service) {
        try {
            $list = $service->all();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取群信息
     *
     * @param GroupChatService $service
     * @param GroupChatValidate $validate
     */
    public function groupChatInfo(GroupChatService $service, GroupChatValidate $validate) {
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
     * 添加群
     *
     * @param GroupChatService $service
     * @param GroupChatValidate $validate
     */
    public function addGroupChat(GroupChatService $service, GroupChatValidate $validate) {
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
     * 更新群
     *
     * @param GroupChatService $service
     * @param GroupChatValidate $validate
     */
    public function updateGroupChat(GroupChatService $service, GroupChatValidate $validate) {
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
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn("更新成功");
    }

    /**
     * 删除群
     *
     * @param GroupChatService $service
     * @param GroupChatValidate $validate
     */
    public function delGroupChat(GroupChatService $service, GroupChatValidate $validate) {
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
