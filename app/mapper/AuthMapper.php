<?php


namespace app\mapper;


use app\model\Auth;

class AuthMapper
{
    /**
     * 添加权限
     * @param $data
     * @return int
     */
    public function addAuth($data) {
        return (new Auth())->insertAll($data);
    }

    /**
     * 删除权限
     * @param $where
     * @return bool
     * @throws \Exception
     */
    public function delAuth($where) {
        return Auth::where($where)->delete();
    }

    /**
     * 根据角色获取权限
     * @param $role_id
     * @return array
     */
    public function getAuthByRoleId($role_id) {
        return Auth::where(["role_id" => $role_id])->column("rule_id");
    }
}