<?php


namespace app\admin\service;


use app\mapper\RoleAuthFieldsMapper;
use think\facade\Db;

class RoleAuthFieldsService extends BaseService
{
    protected $mapper = RoleAuthFieldsMapper::class;

    /**
     * 分配列权限
     *
     * @param $param
     * @return bool
     */
    public function assignRoleAuthField($param) {
        Db::startTrans();
        try {
            $res = $this->deleteBy(["role_id" => $param["role_id"]]);
            if ($res === false)
                throw new \Exception("操作失败");

            $data = [];
            foreach ($param["field_id"] as $k => $v) {
                $data[] = [
                    "role_id" => $param["role_id"],
                    "field_id" => $v,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }
            if (!empty($data)) {
                $res = $this->addAll($data);
                if (!$res)
                    throw new \Exception("操作失败!");
            }

            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
