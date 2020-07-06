<?php


namespace app\admin\service;


use app\mapper\RoleMapper;

class RoleService
{
    private $mapper;

    public function __construct()
    {
        $this->mapper = new RoleMapper();
    }

    /**
     * 获取所有角色
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allRole() {
        return $this->mapper->allRole();
    }

    /**
     * 根据id获取指定角色
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRole($id) {
        return $this->mapper->getRole($id);
    }

    /**
     * 添加角色
     * @param $data
     * @return \app\model\Role|\think\Model
     */
    public function addRole($data) {
        return $this->mapper->addRole($data);
    }

    /**
     * 更新角色
     * @param $data
     * @return \app\model\Role
     */
    public function updateRole($data) {
        unset($data['create_time']);
        $data['update_time'] = time();
        return $this->mapper->updateRole($data);
    }

    /**
     * 删除角色
     * @param $ids
     * @return bool
     */
    public function delRole($ids) {
        return $this->mapper->delRole($ids);
    }
}