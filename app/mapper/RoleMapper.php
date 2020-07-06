<?php


namespace app\mapper;


use app\model\Role;

class RoleMapper
{
    /**
     * 获取全部角色
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allRole() {
        return Role::select();
    }

    /**
     * 根据id获取指定记录
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRole($id) {
        return Role::find($id);
    }

    /**
     * 添加角色
     * @param $data
     * @return Role|\think\Model
     */
    public function addRole($data) {
        return Role::create($data);
    }

    /**
     * 更新角色
     * @param $data
     * @return Role
     */
    public function updateRole($data) {
        return Role::update($data);
    }

    /**
     * 删除角色
     * @param $ids
     * @return bool
     */
    public function delRole($ids) {
        return Role::destroy($ids);
    }
}