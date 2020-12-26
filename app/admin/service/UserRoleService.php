<?php


namespace app\admin\service;


use app\mapper\RoleAuthRowMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\UserMapper;
use app\mapper\UserRoleMapper;
use think\Exception;
use think\facade\Db;

class UserRoleService extends BaseService
{
    protected $mapper = UserRoleMapper::class;
    private $userMapper;

    private $map = [
        1 => "user_user_manager_id",
        5 => "user_customer_id",
        6 => "user_biller_id",
        7 => "user_almighty_id",
        8 => "user_partner_id",
        9 => "user_part_time_editor_id",
        10 => "user_commissioner_id",
        11 => "user_maintain_id",
        12 => "user_manager_id"
    ];

    public function __construct()
    {
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
        $data = (new UserRoleMapper())->getList()->toArray();
        foreach ($data as $k => $v) {
            $roles = "";
            foreach ($v["roles"] as $idx => $item) {
                $roles .= $item["role_name"]."、";
            }
            $data[$k]["roles"] = trim($roles, "、");
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
        return (new UserRoleMapper())->getOne($data);
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
            if (isset($data["password"]) && !empty($data["password"])) {
                $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
            }else {
                $data["password"] = password_hash("123456", PASSWORD_DEFAULT);
            }

            $user = $this->userMapper->add($data);
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
            $res = $this->addAll($user_role_data);
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

            $res = $this->userMapper->updateBy($data);
            if ($res === false)
                throw new Exception("更新失败!!");

            # 获取该用户之前的角色信息
            $roles = (new UserRoleMapper())->columnBy(["user_id" => $data["user_id"]], "role_id");
            $common = array_intersect($roles, $user_role);
            # 新增的角色
            $ins = array_diff($user_role, $common);
            $insData = [];
            foreach ($ins as $k => $v) {
                if (in_array($v, array_keys($this->map))) {
                    $item = [
                        "type" => $this->map[$v],
                        "type_id" => $data["user_id"],
                        "role_id" => 1,
                        "create_time" => time(),
                        "update_time" => time()
                    ];
                    $insData[] = $item;
                }
            }
            # 删除的角色
            $del = array_diff($roles, $common);
            $delType = [];
            foreach ($del as $k => $v) {
                $delType[] = $this->map[$v];
            }
            # 删除不必要的权限
            $res = (new RoleAuthRowMapper())->deleteBy(["type" => $delType, "type_id" => $del]);
            if ($res === false)
                throw new \Exception("更新失败!!");
            $res = (new UserAuthRowMapper())->deleteBy(["type" => $delType, "type_id" => $del]);
            if ($res === false)
                throw new \Exception("更新失败!!");
            # 给管理层赋权
            $res = (new RoleAuthRowMapper())->addAll($insData);
            if (!$res)
                throw new \Exception("更新失败啦!!");


            $user_role_data = [];
            foreach ($user_role as $item) {
                $user_role_data[] = [
                    "role_id" => $item,
                    "user_id" => $data["user_id"]
                ];
            }

            $res = $this->deleteBy(["user_id" => $data["user_id"]]);
            if ($res === false)
                throw new Exception("更新失败啦");

            $res = $this->addAll($user_role_data);
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
            $res = $this->userMapper->deleteBy(["id" => $data["user_id"]]);
            if ($res === false)
                throw new Exception("删除失败");

            $res = $this->deleteBy(["user_id" => $data["user_id"]]);
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
        return $this->userMapper->updateBy($data);
    }
}
