<?php


namespace app\automation\service;


use app\BaseService;
use app\mapper\UserEngineersMapper;

class UserEngineersService extends BaseService
{
    protected $mapper = UserEngineersMapper::class;

    /**
     * 工程师登录
     * @param $param
     * @return false|mixed
     */
    public function login($param) {
        $where = ["qq|phone" => $param["number"]];
        $exits = (new UserEngineersMapper())->findUser($where);
        if (!$exits)
            return false;
        if (!password_verify($param['pwd'], $exits['password']))
            return false;
        return $exits;
    }
}
