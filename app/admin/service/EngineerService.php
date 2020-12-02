<?php


namespace app\admin\service;


use app\mapper\EngineerMapper;
use app\mapper\OrdersMapper;
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
    public function engineer($param) {
        # 构建查询条件
        $where = [];
        $engineer = null;
        $searchField = $param['search_field']??"";
        if (isset($param["profession_id"]) && !empty($param["profession_id"])) {
            $profession_id = [];
            foreach ($param["profession_id"] as $k => $v) {
                $profession_id[] = $v[count($v) - 1];
            }
            $where[] = ["profession_id", "in", $profession_id];
        }
        if (isset($param["personnel_id"]) && !empty($param["personnel_id"]))
            $where[] = ["personnel_id", "=", $param["personnel_id"]];
        if (isset($param["tendency_id"]) && !empty($param["tendency_id"]))
            $where[] = ["tendency_id", "=", $param["tendency_id"]];
        if ((isset($param["time_type"]) && !empty($param["time_type"]))
            &&  (isset($param["search_time"]) && !empty($param["search_time"]))) {
            if ($param["time_type"] == "create_time") {
                $where[] = [$param["time_type"], ">=", strtotime($param["search_time"][0])];
                $where[] = [$param["time_type"], "<=", strtotime($param["search_time"][1])];
            }
            if ($param["time_type"] == "handover_time") {
                $map = [
                    ["create_time", ">=", strtotime($param["search_time"][0])],
                    ["create_time", "<=", strtotime($param["search_time"][1])]
                ];
                $engineer = (new OrdersMapper())->columnBy($map, "engineer_id");
            }
            if ($param["time_type"] == "delivery_time") {
                $map = [
                    ["delivery_time", ">=", strtotime($param["search_time"][0])],
                    ["delivery_time", "<=", strtotime($param["search_time"][1])]
                ];
                $engineer = (new OrdersMapper())->columnBy($map, "engineer_id");
            }
            if (!is_null($engineer)) {
                $where[] = ["id", "in", $engineer];
            }
        }
        # engineer_view 试图
        $list = Db::table("engineer_view")
            ->where($where)
            ->where("evaluation|group_chat_name|software_name|degree_name|contact_qq|qq_nickname|contact_phone|wechat|wechat_nickname|alipay|name|school_name", "like", "%{$searchField}%")
            ->where(["is_delete" => 0])->order("id desc")->select()->toArray();
        # 查询所有用户
        $user = (new UserMapper())->all("id, name");
        $user = array_combine(array_column($user, "id"), array_column($user, "name"));
        foreach ($list as $k => $v) {
            $list[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $list[$k]["personnel_name"] = $user[$v["personnel_id"]];
            $list[$k]["personnel_manager_name"] = $user[$v["personnel_manager_id"]];
//            $list[$k]["personnel_train_name"] = $user[$v["personnel_train_id"]];
            $list[$k]["join_days"] = ceil((time() - $v["create_time"]) / (3600 * 24));
        }
        return $list;
    }
}
