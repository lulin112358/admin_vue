<?php


namespace app\mapper;


use app\model\Engineer;
use think\facade\Db;

class EngineerMapper extends BaseMapper
{
    protected $model = Engineer::class;

    /**
     * 查看启用人数
     * @param $where
     * @return array
     */
    public function turnOnCount($where) {
        return Db::table(Db::table("orders")->group("engineer_id")->buildSql())->alias("o")
            ->join(["engineer" => "e"], "e.id=o.engineer_id")
            ->where($where)
            ->column("biller");
    }
}
