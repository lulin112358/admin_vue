<?php


namespace app\admin\controller;


use app\admin\service\CategoryService;
use app\Code;
use app\validate\CategoryValidate;

class Category extends Base
{
    /**
     * 列表
     * @param CategoryService $service
     */
    public function list(CategoryService $service) {
        try {
            $list = $service->list("id, pid, cate_name");
            $list = generateTree($list);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 添加
     * @param CategoryService $service
     * @param CategoryValidate $validate
     */
    public function add(CategoryService $service, CategoryValidate $validate) {
        $data = input("param.");
        if ($validate->scene("save")->check($data))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->add($data);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");
        $this->ajaxReturn("添加成功");
    }

    /**
     * 更新
     * @param CategoryService $service
     * @param CategoryValidate $validate
     */
    public function update(CategoryService $service, CategoryValidate $validate) {
        $data = input("param.");
        if ($validate->scene("save")->check($data))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $where = ["id" => $data["id"]];
            unset($data["id"]);
            $res = $service->update($data, $where);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }

    /**
     * 删除
     * @param CategoryService $service
     * @param CategoryValidate $validate
     */
    public function del(CategoryService $service, CategoryValidate $validate) {
        $data = input("param.");
        if ($validate->scene("del")->check($data))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->del(["id" => $data["id"]]);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");
        $this->ajaxReturn("操作成功");
    }
}