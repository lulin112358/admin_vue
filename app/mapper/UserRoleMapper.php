<?php


namespace app\mapper;


use app\model\User;
use app\model\UserRole;

class UserRoleMapper extends BaseMapper
{
    protected $model = UserRole::class;
    /**
     * 获取用户/角色关联列表
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList() {
        # 预加载查询
        return User::with(["roles" => function($query) {
            $query->field("roles.id, roles.role_name");
        }])->field("id, user_name, name, codename, department, create_time, update_time, status")->select();
    }

    /**
     * 获取用户/角色信息
     * @param $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($data) {
        return User::where(["id" => $data["user_id"]])->with(["roles" => function($query) {
            $query->field("roles.id, roles.role_name");
        }])->field("id, user_name, department, name, codename, create_time, update_time, status, work_nature, engineer_id, attendance_group_id, department_code")->find();
    }
}
