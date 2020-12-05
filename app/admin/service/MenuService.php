<?php


namespace app\admin\service;


use app\mapper\AuthMapper;
use app\mapper\MenusMapper;
use app\mapper\UserRoleMapper;

class MenuService extends BaseService
{
    protected $mapper = MenusMapper::class;

    /**
     * 获取所有菜单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allMenu() {
        $data = $this->all("*", "sort asc");
        return generateTreeText($data);
    }

    public function menuTree() {
        $data = $this->all("*", "sort asc");
        return generateTree($data);
    }

    /**
     * 更新菜单
     * @param $data
     * @return \app\model\Menus
     */
    public function updateMenu($data) {
        unset($data['create_time']);
        $data['update_time'] = time();
        return $this->updateBy($data);
    }

    /**
     * 根据用户获取菜单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuByUser() {
        $role_ids = (new UserRoleMapper())->columnBy(["user_id" => request()->uid], "role_id");
        if (in_array(1, $role_ids)) {
            return generateMenu($this->selectBy(["is_show" => 1], "icon, path, name, id, pid", "sort asc"));
        }
        $rule_ids = (new AuthMapper())->columnBy(["role_id" => $role_ids], 'rule_id');
        return generateMenu($this->selectBy(["id" => $rule_ids, "is_show" => 1], "id, pid, path, name, icon", "sort asc"));
    }
}
