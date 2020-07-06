<?php


namespace app\admin\service;


use app\mapper\UserMapper;
use app\mapper\UserRoleMapper;
use think\Exception;
use think\facade\Db;

class UserRoleService
{
    private $mapper;
    private $userMapper;

    public function __construct()
    {
        $this->mapper = new UserRoleMapper();
        $this->userMapper = new UserMapper();
    }

    /**
     * 获取用户/角色关联列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList() {
        $data = $this->mapper->getList()->toArray();
        foreach ($data as $k => $v) {
            $roles = "";
            foreach ($v["roles"] as $idx => $item) {
                $roles .= $item["role_name"].",";
            }
            $data[$k]["roles"] = trim($roles, ",");
        }
        return $data;
    }

    /**
     * 获取用户/角色信息
     * @param $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($data) {
        return $this->mapper->getOne($data);
    }

    /**
     * 添加用户
     * @param $data
     * @return bool
     */
    public function addUser($data) {
        Db::startTrans();
        try {
            $user_role = $data["role_id"];
            unset($data["role_id"]);
            $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
            $is_exits = (new UserMapper())->getUserByName($data);
            if ($is_exits)
                throw new Exception("已存在该用户名");

            $user = $this->userMapper->addUser($data);
            if (!$user)
                throw new Exception("添加失败");

            $user_role_data = [];
            foreach ($user_role as $item) {
                $user_role_data[] = [
                    "role_id" => $item,
                    "user_id" => $user->id,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }
            $res = $this->mapper->addData($user_role_data);
            if (!$res)
                throw new Exception("添加失败!");

            Db::commit();
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 更新数据
     * @param $data
     * @return bool
     */
    public function updateUser($data) {
        Db::startTrans();
        try {
            $user_role = $data["role_id"];
            unset($data["role_id"]);
            unset($data["roles"]);
            unset($data["create_time"]);
            $data["update_time"] = time();

            if (isset($data["password"]) && !empty($data["password"]))
                $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
            if (isset($data["password"]) && empty($data["password"]))
                unset($data["password"]);
            $is_exits = (new UserMapper())->getUserByName($data);
            if ($is_exits)
                throw new Exception("已存在该用户名");

            $res = $this->userMapper->updateUser($data);
            if ($res === false)
                throw new Exception("更新失败");

            $user_role_data = [];
            foreach ($user_role as $item) {
                $user_role_data[] = [
                    "role_id" => $item,
                    "user_id" => $data["user_id"]
                ];
            }

            $res = $this->mapper->delData(["user_id" => $data["user_id"]]);
            if ($res === false)
                throw new Exception("更新失败啦");

            $res = $this->mapper->addData($user_role_data);
            if (!$res)
                throw new Exception("更新失败!");

            Db::commit();
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 删除数据
     * @param $data
     * @return bool
     */
    public function delData($data) {
        Db::startTrans();
        try {
            $res = $this->userMapper->delUser($data["user_id"]);
            if ($res === false)
                throw new Exception("删除失败");

            $res = $this->mapper->delData(["user_id" => $data["user_id"]]);
            if ($res === false)
                throw new Exception("删除失败!");

            Db::commit();
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 更新用户状态
     * @param $data
     * @return \app\model\User
     */
    public function updateStatus($data) {
        return $this->userMapper->updateUser($data);
    }
}