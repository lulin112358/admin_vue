<?php


namespace app\engineer\service;


use app\BaseService;
use app\mapper\UserEngineersMapper;

class UserEngineerService extends BaseService
{
    /**
     * 登录稿费核定系统
     * @param $param
     * @return array|false|\think\Model
     */
    public function login($param) {
        $where = ["phone" => $param["user_name"]];
        $exits = (new UserEngineersMapper())->findUser($where);
        if (!$exits)
            return false;
        if (!password_verify($param['password'], $exits['password']))
            return false;
        return $exits;
    }

    /**
     * 修改密码
     * @param $param
     * @return mixed|string
     */
    public function updatePwd($param) {
        # 查询原密码是否正确
        $old = $this->findBy(["engineer_id" => request()->uid], "password")["password"];
        if (!password_verify($param["old_pwd"], $old))
            return "原密码错误";
        # 修改密码
        return $this->updateWhere(["engineer_id" => request()->uid], ["password" => password_hash($param["pwd"], PASSWORD_DEFAULT)]);
    }
}
