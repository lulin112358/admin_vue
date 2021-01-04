<?php


namespace app\admin\service;


use app\mapper\OrdersMainMapper;
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
     * 修改密码
     *
     * @param $param
     * @return mixed|string
     */
    public function updatePwd($param) {
        # 查询原密码是否正确
        $old = $this->findBy(["id" => request()->uid], "password")["password"];
        if (!password_verify($param["old_pwd"], $old))
            return "原密码错误";
        # 修改密码
        return $this->updateWhere(["id" => request()->uid], ["password" => password_hash($param["pwd"], PASSWORD_DEFAULT)]);
    }

    /**
     * 获取所有分组用户数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allGroupUsers($param) {
        $data = (new UserMapper())->allGroupUsers(["r.role_name" => array_keys($this->map)]);
        $tmp = [];
        foreach ($data as $k => $v) {
            $v["id"] = $this->map[$v["role_name"]]."/".$v["id"];
            $tmp[$v["role_name"]][] = $v;
        }
        # 未发订单权限控制
        $tmp["发单人事"][] = [
            "id" => 'user_biller_id/0',
            'name' => '未定',
            'role_name' => '发单人事'
        ];
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

    /**
     * 全能客服排序列表
     * @return array
     */
    public function almightyUserSort() {
        $list = $this->groupUsers(7);
        $sort = array_count_values((new OrdersMainMapper())->columnBy(["customer_id" => request()->uid], "customer_manager"));
        arsort($sort);
        $retData = [];
        foreach ($sort as $k => $v) {
            foreach ($list as $key => $val) {
                if ($val["id"] == $k) {
                    $retData[] = $val;
                }
            }
        }

        foreach ($list as $k => $v) {
            if (!in_array($v["id"], array_keys($sort))) {
                $retData[] = $v;
            }
        }
        return $retData;
    }
}
