<?php


namespace app\admin\service;


use app\mapper\UserMapper;

class UserService
{
    private $mapper;
    public function __construct()
    {
        $this->mapper = new UserMapper();
    }

    /**
     * 根据用户名获取用户信息
     * @param $param
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserByName($param) {
        return $this->mapper->getUserByName($param);
    }

    /**
     * 市场专员
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function commissioner() {
        return $this->mapper->commissioner();
    }

    /**
     * 市场经理
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manager() {
        return $this->mapper->manager();
    }

    /**
     * 市场维护
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function maintain() {
        return $this->mapper->maintain();
    }
}