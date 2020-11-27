<?php


namespace app\admin\service;


use app\mapper\RoleAuthFieldsMapper;
use app\mapper\UserAuthFieldsMapper;
use app\mapper\UserRoleMapper;

class UserAuthFieldsService extends BaseService
{
    protected $mapper = UserAuthFieldsMapper::class;

    /**
     * 获取该用户权限列
     * @return array|mixed
     */
    public function userAuthFields($param) {
        # 获取该用户所有角色
        $role = (new UserRoleMapper())->columnBy(["user_id" => $param["uid"]], "role_id");
        # 获取该用户角色所有列权限
        $columns = (new RoleAuthFieldsMapper())->columnBy(["role_id" => $role], "field_id");
        # 查询该用户独有权限
        $userAuth = $this->selectBy(["user_id" => $param["uid"]], "field_id, type");
        # 根据该用户独有权限进行增删
        foreach ($userAuth as $k => $v) {
            if ($v["type"] == 1) {
                $columns[] = $v["field_id"];
            }else {
                $key = array_search($v["field_id"], $columns);
                unset($columns[$key]);
            }
        }
        return array_values($columns);
    }


    public function assignFields($param) {
        # 获取该用户所有角色
        $role = (new UserRoleMapper())->columnBy(["user_id" => $param["uid"]], "role_id");
        # 获取该用户角色所有列权限
        $columns = (new RoleAuthFieldsMapper())->columnBy(["role_id" => $role], "field_id");
        # 取差集
        $diff1 = array_diff($param["field_id"], $columns);
        $diff2 = array_diff($columns, $param["field_id"]);
        $diff = array_merge($diff1, $diff2);
        $insertData = [];
        foreach ($diff as $k => $v) {
            if (in_array($v, $columns)) {       # 删除
                $insertData[] = [
                    "user_id" => $param["uid"],
                    "field_id" => $v,
                    "type" => 0,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }else {
                $insertData[] = [
                    "user_id" => $param["uid"],
                    "field_id" => $v,
                    "type" => 1,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }
        }
        # 写入数据
        return (new UserAuthFieldsMapper())->assignFields($param, $insertData);
    }
}
