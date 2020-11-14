<?php


namespace app\mapper;


use think\facade\Db;

class BaseMapper
{
    /**
     * 模型
     *
     * @var
     */
    protected $model;


    /**
     * 查询所有数据
     *
     * @param string $field
     * @return mixed
     */
    public function all($field = "*", $order = "") {
        return $this->model::field($field)->order($order)->select()->toArray();
    }

    /**
     * 添加数据
     *
     * @param $data
     * @return mixed
     */
    public function add($data) {
        return $this->model::create($data);
    }

    /**
     * 添加所有
     *
     * @param $data
     * @return int
     */
    public function addAll($data) {
        return (new $this->model())->saveAll($data);
    }

    /**
     * 统计数据数量
     *
     * @param array $where
     * @return mixed
     */
    public function countBy($where = [], $field = "*") {
        return $this->model::where($where)->field($field)->count();
    }

    /**
     * 按条件查找单条记录
     *
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function findBy($where, $field = "*") {
        return $this->model::where($where)->field($field)->find();
    }

    /**
     * 按条件查找符合条件的所有记录
     *
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function selectBy($where, $field = "*", $order = "") {
        return $this->model::where($where)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 按条件更新
     *
     * @param array $where
     * @param null $data
     * @return mixed
     */
    public function updateWhere($where = [], $data = null) {
        return $this->model::where($where)->update($data);
    }

    /**
     * 更新
     *
     * @param $data
     * @return mixed
     */
    public function updateBy($data) {
        return $this->model::update($data);
    }

    /**
     * 指定条件删除
     *
     * @param $where
     * @return mixed
     */
    public function deleteBy($where) {
        return $this->model::where($where)->delete();
    }

    /**
     * 获取某列
     *
     * @param $where
     * @param $column
     * @return mixed
     */
    public function columnBy($where = [], $column = "") {
        return $this->model::where($where)->column($column);
    }

    /**
     * 按条件分页查找
     *
     * @param $where
     * @param string $field
     * @param int $limit
     * @param false $simple
     * @return mixed
     */
    public function pageBy($where, $field = "*", $limit = 15, $simple = false) {
        return $this->model::where($where)->field($field)->paginate($limit, $simple);
    }

}
