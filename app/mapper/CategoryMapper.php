<?php


namespace app\mapper;


use app\model\Category;
use think\facade\Db;

class CategoryMapper extends BaseMapper
{
    protected $model = Category::class;

    /**
     * 查询提示语
     * @param $param
     * @return mixed
     */
    public function placeholder($param) {
        $pid = Db::table("category")->where("id", "=", $param["id"])->value("pid");
        if ($pid == 0) {
            return Db::table("category")->where("id", "=", $param["id"])->value("placeholder");
        }
        return Db::table("category")->where("id", "=", $pid)->value("placeholder");
    }
}
