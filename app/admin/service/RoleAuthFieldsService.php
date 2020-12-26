<?php


namespace app\admin\service;


use app\mapper\RoleAuthFieldsEditMapper;
use app\mapper\RoleAuthFieldsMapper;
use think\facade\Db;

class RoleAuthFieldsService extends BaseService
{
    protected $mapper = RoleAuthFieldsMapper::class;

    /**
     * 获取角色的权限
     * @param $param
     * @return array
     */
    public function roleAuthFieldInfo($param) {
        $fields = $this->columnBy(["role_id" => $param["role_id"]], "field_id");
        $editFields = (new RoleAuthFieldsEditMapper())->columnBy(["role_id" => $param["role_id"]], "field_id");
        foreach ($editFields as $k => $v) {
            $editFields[$k] = "edit_".$v;
        }
        return array_merge($fields, $editFields);
    }

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
            $res = (new RoleAuthFieldsEditMapper())->deleteBy(["role_id" => $param["role_id"]]);
            if ($res === false)
                throw new \Exception("操作失败");

            $data = [];
            $editData = [];
            foreach ($param["field_id"] as $k => $v) {
                if (is_int($v)) {
                    $data[] = [
                        "role_id" => $param["role_id"],
                        "field_id" => $v,
                        "create_time" => time(),
                        "update_time" => time()
                    ];

                }else{
                    $editData[] = [
                        "role_id" => $param["role_id"],
                        "field_id" => explode("edit_", $v)[1],
                        "create_time" => time(),
                        "update_time" => time()
                    ];
                }
            }
            if (!empty($data)) {
                $res = $this->addAll($data);
                if (!$res)
                    throw new \Exception("操作失败!");
            }
            if (!empty($editData)) {
                $res = (new RoleAuthFieldsEditMapper())->addAll($editData);
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
