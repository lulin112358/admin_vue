<?php


namespace app\admin\service;


use app\mapper\UserMapper;
use app\mapper\UserRoleMapper;

class UserService extends BaseService
{
    protected $mapper = UserMapper::class;

    # 映射关系 / 需要与Base控制器中的$tableMap映射关系保持一致
    private $map = [
        "管理层" => "user_user_manager_id",
        "接单客服" => "user_customer_id",
        "发单人事" => "user_biller_id",
        "全能客服" => "user_almighty_id",
        "上游合作代理" => "user_partner_id",
        "兼职编辑" => "user_part_time_editor_id",
        "市场专员" => "user_commissioner_id",
        "市场维护" => "user_maintain_id",
        "市场经理" => "user_manager_id",
    ];

    /**
     * 获取指定分组用户
     * @return array
     */
    public function groupUsers($role_id) {
        return (new UserMapper())->groupUsers($role_id);
    }

    /**
     * 获取所有分组用户数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allGroupUsers($param) {
        $data = (new UserMapper())->allGroupUsers();
        $tmp = [];
        foreach ($data as $k => $v) {
            $v["id"] = $this->map[$v["role_name"]]."/".$v["id"];
            $tmp[$v["role_name"]][] = $v;
        }
        # 去除不必要的组
        if (isset($param["user_id"]) && !empty($param["user_id"])) {
            # 获取用户所属权限组
            $roles = (new UserRoleMapper())->columnBy(["user_id" => $param["user_id"]], "role_id");
            if (!in_array(1, $roles)) {
                unset($tmp["市场专员"]);
                unset($tmp["市场维护"]);
                unset($tmp["市场经理"]);
            }
        }
        if (isset($param["role_id"]) && !empty($param["role_id"])) {
            if ($param["role_id"] != 1) {
                unset($tmp["市场专员"]);
                unset($tmp["市场维护"]);
                unset($tmp["市场经理"]);
            }
        }
        $retData = [];
        foreach ($tmp as $k => $v) {
            $retData[] = [
                "name" => $k,
                "id" => $this->map[$k],
                "children" => $v
            ];
        }
        return $retData;
    }
}
