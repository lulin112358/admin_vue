<?php


namespace app\admin\service;


use app\mapper\EngineerMapper;
use app\mapper\UserMapper;
use think\facade\Db;

class EngineerService extends BaseService
{
    protected $mapper = EngineerMapper::class;

    /**
     * 查询所有工程师
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function engineer() {
        # engineer_view 试图
        $list = Db::table("engineer_view")->select()->toArray();
        # 查询所有用户
        $user = (new UserMapper())->all("id, name");
        $user = array_combine(array_column($user, "id"), array_column($user, "name"));
        foreach ($list as $k => $v) {
            $list[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $list[$k]["personnel_name"] = $user[$v["personnel_id"]];
            $list[$k]["personnel_manager_name"] = $user[$v["personnel_manager_id"]];
            $list[$k]["personnel_train_name"] = $user[$v["personnel_train_id"]];
            $list[$k]["join_days"] = ceil((time() - $v["create_time"]) / (3600 * 24));
        }
        return $list;
    }
}
