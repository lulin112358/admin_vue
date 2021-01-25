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
    public function allGroupUsers($where) {
        return Db::table("user_role")->alias("ur")
            ->join(["user" => "u"], "u.id=ur.user_id")
            ->join(["roles" => "r"], "r.id=ur.role_id")
            ->where($where)
            ->field("u.id, u.name, r.role_name")
            ->select()->toArray();
    }

    /**
     * 获取用户数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userData($where = []) {
        return User::with(["extend" => function($query) {
            $query->field("*");
        }])->where($where)->field("*")->select()->toArray();
    }

    /**
     * 获取用户上班时间
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function workTime() {
        return Db::table("user")->alias("u")
            ->join(["attendance_group" => "ag"], "ag.id=u.attendance_group_id")
            ->where(["u.id" => request()->uid])
            ->field("ag.start_time, ag.end_time")
            ->find();
    }
}
