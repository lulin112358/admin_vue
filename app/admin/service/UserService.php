<?php


namespace app\admin\service;


use app\mapper\UserMapper;

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
    public function allGroupUsers() {
        $data = (new UserMapper())->allGroupUsers();
        $tmp = [];
        foreach ($data as $k => $v) {
            $tmp[$v["role_name"]][] = $v;
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
