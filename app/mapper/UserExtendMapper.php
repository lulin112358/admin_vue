<?php


namespace app\mapper;


use app\model\UserExtend;
use think\facade\Db;
use think\model\Relation;

class UserExtendMapper extends BaseMapper
{
    protected $model = UserExtend::class;

    /**
     * 获取用户扩展信息
     * @param $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userExtendInfo($where) {
        return Db::table("user_extend")->alias("ue")
            ->join(["user" => "u"], "u.id=ue.user_id", "right")
            ->join(["school" => "s"], "s.id=ue.school_id", "left")
            ->field("s.name as school_name, u.name, u.department, u.status, u.create_time as tmp_entry_time, ue.*")
            ->where($where)
            ->find();
    }
}
