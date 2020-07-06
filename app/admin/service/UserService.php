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
}