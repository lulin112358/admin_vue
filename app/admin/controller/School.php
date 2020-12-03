<?php


namespace app\admin\controller;


use app\admin\service\SchoolService;
use app\Code;
use app\validate\SchoolValidate;

class School extends Base
{
    /**
     * 学校列表
     *
     * @param SchoolService $service
     */
    public function schools(SchoolService $service) {
        $param = input("param.");
        try {
            $list = $service->schools($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }

    /**
     * 搜索学校
     *
     * @param SchoolService $service
     * @param SchoolValidate $validate
     */
    public function searchSchools(SchoolService $service, SchoolValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("search")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $list = $service->selectBy([["name", "like", "%{$param['school_name']}%"]], "id, name");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($list);
    }
}
