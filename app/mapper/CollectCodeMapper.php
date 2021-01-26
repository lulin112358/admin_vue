<?php


namespace app\mapper;


use app\model\CollectCode;
use think\facade\Db;

class CollectCodeMapper extends BaseMapper
{
    protected $model = CollectCode::class;

    /**
     * 获取用户能够看到的收款码
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userCollectCode($where) {
        return Db::table("collect_code_user")->alias("ccu")
            ->join(["collect_code" => "cc"], "cc.id=ccu.collect_id")
            ->where($where)
            ->field("cc.id, cc.collect_code, cc.title")
            ->select()->toArray();
    }
}
