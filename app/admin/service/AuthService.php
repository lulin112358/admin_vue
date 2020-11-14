<?php


namespace app\admin\service;


use app\mapper\AuthMapper;
use think\Exception;
use think\facade\Db;

class AuthService extends BaseService
{
    protected $mapper = AuthMapper::class;

    /**
     * 添加/修改权限
     * @param $data
     * @return int
     */
    public function saveAuth($data) {
        Db::startTrans();
        try {
            $where = ["role_id" => $data["role_id"]];
            $res = (new $this->mapper())->deleteBy($where);
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
                $res = (new $this->mapper())->addAll($ins);
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
}
