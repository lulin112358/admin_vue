<?php


namespace app\admin\service;


use app\mapper\RoleAuthRowMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\UserRoleMapper;

class UserAuthRowService extends BaseService
{
    protected $mapper = UserAuthRowMapper::class;

    /**
     * 获取该用户行权限
     * @param $param
     * @return array
     */
    public function userAuthRow($param) {
        # 获取该用户所有角色
        $role = (new UserRoleMapper())->columnBy(["user_id" => $param["uid"]], "role_id");
        # 获取该用户角色所有权限
        $row = (new RoleAuthRowMapper())->selectBy(["role_id" => $role], "type, type_id");
        $rowData = [];
        foreach ($row as $k => $v) {
            $rowData[] = $v["type"].'/'.$v["type_id"];
        }
        # 查询该用户独有权限
        $userAuth = $this->selectBy(["user_id" => $param["uid"]], "type, type_id, status");
        foreach ($userAuth as $k => $v) {
            if ($v["status"] == 1) {
                $rowData[] = $v["type"].'/'.$v["type_id"];
            }else {
                $key = array_search($v["type"].'/'.$v["type_id"], $rowData);
                unset($rowData[$key]);
            }
        }
        return array_values($rowData);
    }


    /**
     * 绑定权限
     * @param $param
     * @return bool
     */
    public function assignAuth($param) {
        # 获取该用户所有角色
        $role = (new UserRoleMapper())->columnBy(["user_id" => $param["uid"]], "role_id");
        # 获取该用户角色所有权限
        $row = (new RoleAuthRowMapper())->selectBy(["role_id" => $role], "type, type_id");
        $rowData = [];
        foreach ($row as $k => $v) {
            $rowData[] = $v["type"].'/'.$v["type_id"];
        }
        # 取差集
        $diff1 = array_diff($param["row_info"], $rowData);
        $diff2 = array_diff($rowData, $param["row_info"]);
        $diff = array_merge($diff1, $diff2);
        $insertData = [];
        foreach ($diff as $k => $v) {
            $typeData = explode("/", $v);
            if (count($typeData) == 2) {
                if (in_array($v, $rowData)) {       # 删除
                    $insertData[] = [
                        "user_id" => $param["uid"],
                        "type" => $typeData[0],
                        "type_id" => $typeData[1],
                        "status" => 0,
                        "create_time" => time(),
                        "update_time" => time()
                    ];
                }else {
                    $insertData[] = [
                        "user_id" => $param["uid"],
                        "type" => $typeData[0],
                        "type_id" => $typeData[1],
                        "status" => 1,
                        "create_time" => time(),
                        "update_time" => time()
                    ];
                }
            }
        }
        # 写入数据
        return (new UserAuthRowMapper())->assignAuth($param, $insertData);
    }
}
