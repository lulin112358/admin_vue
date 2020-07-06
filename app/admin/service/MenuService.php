<?php


namespace app\admin\service;


use app\mapper\AuthMapper;
use app\mapper\MenusMapper;
use app\mapper\UserRoleMapper;

class MenuService
{
    private $mapper;

    public function __construct()
    {
        $this->mapper = new MenusMapper();
    }

    /**
     * 获取所有菜单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allMenu() {
        $data = $this->mapper->allMenu();
        return generateTreeText(collect($data)->toArray());
    }

    public function menuTree() {
        $data = $this->mapper->allMenu()->toArray();
        return generateTree($data);
    }

    /**
     * 获取指定菜单信息
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenu($id) {
        return $this->mapper->getMenu($id);
    }

    /**
     * 添加菜单
     * @param $data
     * @return \app\model\Menus|\think\Model
     */
    public function addMenu($data) {
        return $this->mapper->addMenu($data);
    }

    /**
     * 更新菜单
     * @param $data
     * @return \app\model\Menus
     */
    public function updateMenu($data) {
        unset($data['create_time']);
        $data['update_time'] = time();
        return $this->mapper->updateMenu($data);
    }

    /**
     * 删除菜单
     * @param $ids
     * @return bool
     */
    public function deleteMenu($ids) {
        return $this->mapper->deleteMenu($ids);
    }

    /**
     * 根据用户获取菜单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuByUser() {
        $role_ids = (new UserRoleMapper())->getRoleByUser(request()->user["data"]->uid);
        if (in_array(1, $role_ids)) {
            return generateMenu($this->mapper->allShowMenu()->toArray());
        }
        $rule_ids = (new AuthMapper())->getAuthByRoleId($role_ids);
        return generateMenu($this->mapper->getMenuByWhere(["id" => $rule_ids, "is_show" => 1])->toArray());
    }
}