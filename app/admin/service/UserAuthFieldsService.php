<?php


namespace app\admin\service;


use app\mapper\RoleAuthFieldsEditMapper;
use app\mapper\RoleAuthFieldsMapper;
use app\mapper\UserAuthFieldsEditMapper;
use app\mapper\UserAuthFieldsMapper;
use app\mapper\UserRoleMapper;
use think\facade\Db;

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
        # 获取该用户角色所有编辑列权限
        $editColumns = (new RoleAuthFieldsEditMapper())->columnBy(["role_id" => $role], "field_id");
        # 查询该用户独有编辑列权限
        $userEditAuth = (new UserAuthFieldsEditMapper())->selectBy(["user_id" => $param["uid"]], "field_id, type");

        foreach ($editColumns as $k => $v) {
            $editColumns[$k] = "edit_".$v;
        }

        # 根据该用户独有权限进行增删
        foreach ($userAuth as $k => $v) {
            if ($v["type"] == 1) {
                $columns[] = $v["field_id"];
            }else {
                $key = array_search($v["field_id"], $columns);
                unset($columns[$key]);
            }
        }

        # 根据该用户独有编辑权限进行增删
        foreach ($userEditAuth as $k => $v) {
            if ($v["type"] == 1) {
                $editColumns[] = "edit_".$v["field_id"];
            }else {
                $key = array_search("edit_".$v["field_id"], $editColumns);
                unset($editColumns[$key]);
            }
        }
        return array_merge(array_values($editColumns), array_values(array_unique($columns)));
    }


    public function assignFields($param) {
        # 获取该用户所有角色
        $role = (new UserRoleMapper())->columnBy(["user_id" => $param["uid"]], "role_id");
        # 获取该用户角色所有列权限
        $columns = (new RoleAuthFieldsMapper())->columnBy(["role_id" => $role], "field_id");
        # 获取该用户角色所有编辑列权限
        $editColumns = (new RoleAuthFieldsEditMapper())->columnBy(["role_id" => $role], "field_id");
        $fields = [];
        $editFields = [];
        foreach ($param["field_id"] as $k => $v) {
            if (is_int($v)) {
                $fields[] = $v;
            }else {
                $editFields[] = explode("edit_", $v)[1];
            }
        }
        # 取差集
        $diff1 = array_diff($fields, $columns);
        $diff2 = array_diff($columns, $fields);
        $diff = array_merge($diff1, $diff2);
        # 可编辑列取差集
        $diffEdit1 = array_diff($editFields, $editColumns);
        $diffEdit2 = array_diff($editColumns, $editFields);
        $diffEdit = array_merge($diffEdit1, $diffEdit2);

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
        $editInst = [];
        foreach ($diffEdit as $k => $v) {
            if (in_array($v, $editColumns)) {       # 删除
                $editInst[] = [
                    "user_id" => $param["uid"],
                    "field_id" => $v,
                    "type" => 0,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }else {
                $editInst[] = [
                    "user_id" => $param["uid"],
                    "field_id" => $v,
                    "type" => 1,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }
        }
        # 写入数据
        Db::startTrans();
        try {
            $res = (new UserAuthFieldsMapper())->deleteBy(["user_id" => $param["uid"]]);
            if ($res === false)
                throw new \Exception("操作失败");
            $res = (new UserAuthFieldsMapper())->addAll($insertData);
            if (!$res)
                throw new \Exception("操作失败");

            $res = (new UserAuthFieldsEditMapper())->deleteBy(["user_id" => $param["uid"]]);
            if ($res === false)
                throw new \Exception("操作失败");
            $res = (new UserAuthFieldsEditMapper())->addAll($editInst);
            if (!$res)
                throw new \Exception("操作失败");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
