<?php


namespace app\admin\controller;


use app\admin\service\TaskService;
use app\admin\service\TaskUserService;
use app\Code;
use app\validate\TaskUserValidate;
use app\validate\TaskValidate;

class Task extends Base
{
    /**
     * 获取所有任务
     * @param TaskService $service
     */
    public function tasks(TaskService $service) {
        try {
            $data = $service->tasks();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加任务
     * @param TaskService $service
     * @param TaskValidate $validate
     */
    public function addTask(TaskService $service, TaskValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->addTask($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 更新任务
     * @param TaskService $service
     * @param TaskValidate $validate
     */
    public function updateTask(TaskService $service, TaskValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateTask($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");
        $this->ajaxReturn("更新成功");
    }

    /**
     * 删除任务
     * @param TaskService $service
     * @param TaskValidate $validate
     */
    public function delTask(TaskService $service, TaskValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("delete")->check($param))
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
     * 任务信息
     * @param TaskService $service
     * @param TaskValidate $validate
     */
    public function taskInfo(TaskService $service, TaskValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("info")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->findBy(["id" => $param["id"]]);
            $data["process_time"] = date("Y-m-d H:i:s", $data["process_time"]);
            if (!empty($data["cycle_config"])) {
                $data["cycle_config"] = json_decode($data["cycle_config"], true);
            }
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 更新状态
     * @param TaskService $service
     * @param TaskValidate $validate
     */
    public function updateStatus(TaskService $service, TaskValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("status")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updateWhere(["id" => $param["id"]], ["status" => $param["status"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");
        $this->ajaxReturn("更新成功");
    }

    /**
     * 获取任务用户
     * @param TaskUserService $service
     */
    public function taskUsers(TaskUserService $service, TaskUserValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("taskUser")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->taskUsers($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 分配任务
     * @param TaskUserService $service
     * @param TaskUserValidate $validate
     */
    public function assignTask(TaskUserService $service, TaskUserValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("assignTask")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->assignTask($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "分配失败");
        $this->ajaxReturn("分配成功");
    }

    /**
     * 获取需要审核的列表
     * @param TaskUserService $service
     * @param TaskUserValidate $validate
     */
    public function needAudit(TaskUserService $service, TaskUserValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("taskUser")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $data = $service->needAudit($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 审核通过
     * @param TaskUserService $service
     * @param TaskUserValidate $validate
     */
    public function auditTask(TaskUserService $service, TaskUserValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("auditTask")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->auditTask($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }
}
