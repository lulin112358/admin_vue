<?php


namespace app\admin\service;


use app\mapper\AuthMapper;
use think\Exception;
use think\facade\Db;

class AuthService
{
    private $mapper;

    public function __construct()
    {
        $this->mapper = new AuthMapper();
    }

    /**
     * 添加/修改权限
     * @param $data
     * @return int
     */
    public function saveAuth($data) {
        Db::startTrans();
        try {
            $where = ["role_id" => $data["role_id"]];
            $res = $this->mapper->delAuth($where);
            if ($res === false)
                throw new Exception("操作失败");

            $ins = [];
            foreach ($data["rule_id"] as $k => $v) {
                $ins[] = [
                    "rule_id" => $v,
                    "role_id" => $data["role_id"]
                ];
            }
            if (!empty($ins)) {
                $res = $this->mapper->addAuth($ins);
                if (!$res)
                    throw new Exception("操作失败!");
            }

            Db::commit();
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 根据角色获取权限
     * @param $role_id
     * @return array
     */
    public function getRuleByRole($role_id) {
        return $this->mapper->getAuthByRoleId($role_id);
    }
}