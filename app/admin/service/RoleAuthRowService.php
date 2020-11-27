<?php


namespace app\admin\service;


use app\mapper\RoleAuthRowMapper;
use think\facade\Db;

class RoleAuthRowService extends BaseService
{
    protected $mapper = RoleAuthRowMapper::class;


    /**
     * 获取角色行权限信息
     *
     * @param $param
     * @return array
     */
    public function roleAuthRowInfo($param) {
        $data = $this->selectBy(["role_id" => $param["role_id"]], "type, type_id");
        $retData = [];
        foreach ($data as $k => $v) {
            $retData[] = $v["type"].'/'.$v["type_id"];
        }
        return $retData;
    }


    /**
     * 绑定行权限
     *
     * @param $param
     * @return bool
     */
    public function assignRoleAuthRow($param) {
        Db::startTrans();
        try {
            $res = $this->deleteBy(["role_id" => $param["role_id"]]);
            if ($res === false)
                throw new \Exception("操作失败");

            $data = [];
            foreach ($param["row_info"] as $k => $v) {
                $data[] = [
                    "role_id" => $param["role_id"],
                    "type" => explode("/", $v)[0],
                    "type_id" => explode("/", $v)[1],
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
