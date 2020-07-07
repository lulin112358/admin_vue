<?php


namespace app\admin\service;


use app\mapper\AccountCateMapper;

class AccountCateService
{
    private $mapper;

    public function __construct()
    {
        $this->mapper = new AccountCateMapper();
    }

    /**
     * 添加
     * @param $data
     * @return \app\model\AccountCate|\think\Model
     */
    public function add($data) {
        return $this->mapper->addCate($data);
    }

    /**
     * 更新
     * @param $data
     * @return \app\model\AccountCate
     */
    public function update($data) {
        return $this->mapper->updateCate($data);
    }

    /**
     * 列表
     * @param string $field
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function all($field = "*") {
        return $this->mapper->allCate($field);
    }

    /**
     * 获取指定一个
     * @param array $where
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function one($where = [], $field = "*") {
        return $this->mapper->cateOne($where, $field);
    }

    /**
     * 删除
     * @param $ids
     * @return bool
     */
    public function del($ids) {
        return $this->mapper->delCate($ids);
    }
}