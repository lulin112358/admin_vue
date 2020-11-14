<?php


namespace app\admin\service;


use app\mapper\RoleMapper;

class RoleService extends BaseService
{
    protected $mapper = RoleMapper::class;

    /**
     * 更新角色
     * @param $data
     * @return \app\model\Role
     */
    public function updateRole($data) {
        unset($data['create_time']);
        $data['update_time'] = time();
        return $this->updateBy($data);
    }
}
