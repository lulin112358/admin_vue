<?php


namespace app\admin\service;


use app\mapper\CategoryMapper;

class CategoryService
{
    private $mapper;
    public function __construct()
    {
        $this->mapper = new CategoryMapper();
    }

    /**
     * 获取列表
     * @param $field
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list($field) {
        return $this->mapper->list($field);
    }

    /**
     * 添加
     * @param $data
     * @return \app\model\Category|\think\Model
     */
    public function add($data) {
        return $this->mapper->add($data);
    }

    /**
     * 更新
     * @param $data
     * @param $where
     * @return \app\model\Category
     */
    public function update($data, $where) {
        return $this->mapper->update($where, $data);
    }

    /**
     * 删除
     * @param $where
     * @return bool
     * @throws \Exception
     */
    public function del($where) {
        return $this->mapper->del($where);
    }
}