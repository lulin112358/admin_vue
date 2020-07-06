<?php


namespace app\mapper;


use app\model\Menus;

class MenusMapper
{
    /**
     * 查询所有记录
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allMenu() {
        return Menus::order("sort asc")->select();
    }

    /**
     * 查询所有需要展示记录
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allShowMenu() {
        return Menus::where(["is_show" => 1])->field("icon, path, name, id, pid")->order("sort asc")->select();
    }

    /**
     * 查询指定记录
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenu($id) {
        return Menus::find($id);
    }

    /**
     * 添加记录
     * @param $data
     * @return Menus|\think\Model
     */
    public function addMenu($data) {
        return Menus::create($data);
    }

    /**
     * 更新数据
     * @param $data
     * @return Menus
     */
    public function updateMenu($data) {
        return Menus::update($data);
    }

    /**
     * 删除数据
     * @param $ids
     * @return bool
     */
    public function deleteMenu($ids) {
        return Menus::destroy($ids);
    }

    /**
     * 获取菜单
     * @param $where
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuByWhere($where) {
        return Menus::where($where)->field("id, pid, path, name, icon")->order("sort asc")->select();
    }
}