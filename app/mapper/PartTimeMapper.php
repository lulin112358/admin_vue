<?php


namespace app\mapper;


use app\model\PartTime;
use think\facade\Db;

class PartTimeMapper extends BaseMapper
{
    protected $model = PartTime::class;

    /**
     * 获取兼职数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function partTimes($where) {
        return Db::table("part_time")->alias("pt")
            ->join(["user" => "u"], "u.id=pt.user_id")
            ->where($where)
            ->where(["u.work_nature" => [0, 2]])
            ->field("u.work_nature, u.salary, u.name, u.department, pt.*")
            ->select()->toArray();
    }
}
