<?php


namespace app\admin\controller;


use app\admin\service\TypesettingService;
use app\Code;
use TypesettingApiPackage\TTApiManager;

class Typesetting extends Base
{
    /**
     * 获取模板列表
     * @param TTApiManager $apiManager
     */
    public function schemaIdList(TTApiManager $apiManager) {
        $data = $apiManager->schemaIdList()->getData();
        $this->ajaxReturn($data);
    }

    /**
     * 上传文档
     * @param TTApiManager $apiManager
     */
    public function uploadPaper(TypesettingService $service) {
        $param = input("param.");
        try {
            $res = $service->uploadPaper($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "上传失败");
        $this->ajaxReturn("上传成功");
    }

    /**
     * 查询记录
     * @param TTApiManager $apiManager
     */
    public function recordFind(TypesettingService $service) {
        try {
            $service->recordFind();
        }catch (\Exception $exception) {
            echo "成功";
        }
    }

    /**
     * 排版记录
     * @param TypesettingService $service
     */
    public function recordList(TypesettingService $service) {
        try {
            $list = $service->pageBy(["user_id" => request()->uid], "id, title, author, pdf_path, docx_path, status, create_time, update_time, school", 100);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 删除排版记录
     * @param TypesettingService $service
     */
    public function delRecord(TypesettingService $service) {
        $param = input("param.");
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
