<?php


namespace app\mapper;


use app\model\Category;

class CategoryMapper
{
    /**
     * 获取列表
     * @param $field
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list($field) {
        return Category::field($field)->select();
    }

    /**
     * 添加
     * @param $data
     * @return Category|\think\Model
     */
    public function add($data) {
        return Category::create($data);
    }

    /**
     * 更新
     * @param $where
     * @param $data
     * @return Category
     */
    public function update($where, $data) {
        return Category::update($data, $where);
    }

    /**
     * 删除
     * @param $where
     * @return bool
     * @throws \Exception
     */
    public function del($where) {
        return Category::where($where)->delete();
    }
}