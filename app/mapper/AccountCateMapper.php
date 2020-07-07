<?php


namespace app\mapper;


use app\model\AccountCate;

class AccountCateMapper
{
    /**
     * 查询账号类型
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allCate($field = "*") {
        return AccountCate::field($field)->select();
    }

    /**
     * 查询指定账号类型信息
     * @param array $where
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cateOne($where = [], $field = "*") {
        return AccountCate::where($where)->field($field)->find();
    }

    /**
     * 添加账号类型
     * @param $data
     * @return AccountCate|\think\Model
     */
    public function addCate($data) {
        return AccountCate::create($data);
    }

    /**
     * 更新账号类型
     * @param $data
     * @return AccountCate
     */
    public function updateCate($data) {
        return AccountCate::update($data);
    }

    /**
     * 删除账号类型
     * @param $ids
     * @return bool
     */
    public function delCate($ids) {
        return AccountCate::destroy($ids);
    }
}