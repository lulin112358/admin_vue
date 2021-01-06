<?php


namespace app\mapper;


use app\model\UserEngineers;
use think\facade\Db;

class UserEngineersMapper extends BaseMapper
{
    protected $model = UserEngineers::class;
    public function findUser($where) {
        return Db::table("user_engineers")->alias("ue")
            ->join(["engineer" => "e"], "e.id=ue.engineer_id")
            ->where($where)
            ->field("ue.engineer_id, e.qq_nickname, ue.qq, ue.phone, ue.password, e.qq_nickname")
            ->find();
    }
}
