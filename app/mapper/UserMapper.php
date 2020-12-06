<?php


namespace app\mapper;


use app\model\User;
use think\facade\Db;

class UserMapper extends BaseMapper
{
    protected $model = User::class;

    /**
     * 获取指定分组用户
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function groupUsers($role_id) {
        return Db::table("user_role")->alias("ur")
            ->join(["user" => "u"], "u.id=ur.user_id")
            ->where(["ur.role_id" => $role_id, "u.status" => 1])
            ->field("u.id, u.name")
            ->select()->toArray();
    }

    /**
     * 获取所有分组用户数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allGroupUsers() {
        return Db::table("user_role")->alias("ur")
            ->join(["user" => "u"], "u.id=ur.user_id")
            ->join(["roles" => "r"], "r.id=ur.role_id")
            ->field("u.id, u.name, r.role_name")
            ->select()->toArray();
    }
}
