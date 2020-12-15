<?php


namespace app;


class BaseService
{
    protected $mapper;

    public function all($field = "*", $order = "") {
        return (new $this->mapper())->all($field, $order);
    }

    public function add($data) {
        return (new $this->mapper())->add($data);
    }

    public function addAll($data) {
        return (new $this->mapper())->addAll($data);
    }

    public function countBy($where = [], $field = "*") {
        return (new $this->mapper())->countBy($where, $field);
    }

    public function findBy($where, $field = "*", $order = "") {
        return (new $this->mapper())->findBy($where, $field, $order);
    }

    public function selectBy($where, $field = "*", $order = "") {
        return (new $this->mapper())->selectBy($where, $field, $order);
    }

    public function updateWhere($where = [], $data = null) {
        return (new $this->mapper())->updateWhere($where, $data);
    }

    public function updateBy($data) {
        return (new $this->mapper())->updateBy($data);
    }

    public function deleteBy($where) {
        return (new $this->mapper())->deleteBy($where);
    }

    public function columnBy($where = [], $column = "") {
        return (new $this->mapper())->columnBy($where, $column);
    }

    public function pageBy($where, $field = "*", $limit = 15, $simple = false) {
        return (new $this->mapper())->pageBy($where, $field, $limit, $simple);
    }
}
