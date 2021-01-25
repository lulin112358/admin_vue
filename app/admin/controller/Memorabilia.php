<?php


namespace app\admin\controller;


use app\admin\service\MemorabiliaService;
use app\Code;

class Memorabilia extends Base
{
    /**
     * 大事记列表
     * @param MemorabiliaService $service
     */
    public function memorabilia(MemorabiliaService $service) {
        try {
            $data = $service->selectBy([["user_id", "=", request()->uid], ["delete_time", "=", 0]], "*", "create_time desc");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 添加大事记
     * @param MemorabiliaService $service
     */
    public function addMemorabilia(MemorabiliaService $service) {
        $param = input("param.");
        try {
            $res = $service->addMemorabilia($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 更新大事记
     * @param MemorabiliaService $service
     */
    public function updateMemorabilia(MemorabiliaService $service) {
        $param = input("param.");
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
     * 删除大事记
     * @param MemorabiliaService $service
     */
    public function delMemorabilia(MemorabiliaService $service) {
        $param = input("param.");
        try {
            $res = $service->updateWhere(["id" => $param["id"]], ["delete_time" => time()]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");
        $this->ajaxReturn("删除成功");
    }
}
