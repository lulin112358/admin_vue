<?php


namespace app\mapper;


use app\model\User;
use app\model\UserRole;

class UserRoleMapper
{
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
        }])->field("id, user_name, name, create_time, update_time, status")->select();
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
        }])->field("id, user_name, name, create_time, update_time, status")->find();
    }

    /**
     * 添加关联数据
     * @param $data
     * @return int
     */
    public function addData($data) {
        return (new UserRole())->insertAll($data);
    }

    /**
     * 删除关联数据
     * @param $where
     * @return bool
     * @throws \Exception
     */
    public function delData($where) {
        return UserRole::where($where)->delete();
    }


    /**
     * 通过用户获取角色
     * @param $user_id
     * @return array
     */
    public function getRoleByUser($user_id) {
        return UserRole::where(["user_id" => $user_id])->column("role_id");
    }
}