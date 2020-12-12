<?php


namespace app\admin\service;


use app\mapper\EngineerMapper;
use app\mapper\OrdersMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\UserMapper;
use Carbon\Carbon;
use excel\Excel;
use think\facade\Db;

class EngineerService extends BaseService
{
    protected $mapper = EngineerMapper::class;

    /**
     * 搜索工程师
     * @param $param
     * @return mixed
     */
    public function engineerSearch($param) {
        $data = $this->selectBy([["contact_qq", "like", "{$param['query']}%"], ["status", "=", 1], ["is_delete", "=", 0]],
            "id, qq_nickname, contact_qq");
        foreach ($data as $k => $v)
            $data[$k]["qqinfo"] = $v["qq_nickname"]." / ".$v["contact_qq"];

        return $data;
    }

    /**
     * 获取所有工程师(基本信息)
     * @return mixed
     */
    public function engineersBaseInfo() {
        $data = $this->selectBy(["status" => 1, "is_delete" => 0], "id, qq_nickname, contact_qq");
        foreach ($data as $k => $v)
            $data[$k]["qqinfo"] = $v["qq_nickname"]." / ".$v["contact_qq"];

        return $data;
    }

    /**
     * 添加工程师
     * @param $param
     * @return bool
     */
    public function addEngineer($param) {
        Db::startTrans();
        try {
            # 添加工程师
            $res = $this->add($param);
            if (!$res)
                throw new \Exception("添加失败");
            # 添加该行可见权限
            $addData = [
                "type" => "engineer_id",
                "type_id" => $res->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($addData);
            if (!$res)
                throw new \Exception("添加失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }

    }

    /**
     * 查询所有工程师
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function engineer($param, $export = false) {
        Carbon::setLocale("zh");
        # 构建查询条件
        $where = [];
        $engineer = null;
        $searchField = $param['search_field']??"";
        if (isset($param["profession_id"]) && !empty($param["profession_id"])) {
            $profession_id = [];
            foreach ($param["profession_id"] as $k => $v) {
                $v = json_decode($v, true);
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
        if ($export) {
            $list = Db::table("engineer_view")
                ->where($where)
                ->where("evaluation|group_chat_name|software_name|degree_name|contact_qq|qq_nickname|contact_phone|wechat|wechat_nickname|alipay|name|school_name", "like", "%{$searchField}%")
                ->where(["is_delete" => 0])->order("id desc")->select()->toArray();
        }else {
            $list = Db::table("engineer_view")
                ->where($where)
                ->where("evaluation|group_chat_name|software_name|degree_name|contact_qq|qq_nickname|contact_phone|wechat|wechat_nickname|alipay|name|school_name", "like", "%{$searchField}%")
                ->where(["is_delete" => 0])->order("id desc")->paginate(100, true)->items();
        }
        # 查询接稿数量
        $receiveData = (new OrdersMapper())->selectBy([], "engineer_id, create_time", "create_time desc");
        # 今日接稿数量
        $today = collect($receiveData)->where("create_time", ">=", strtotime(date("Y-m-d")))
            ->where("create_time", "<=", time())->toArray();
        if (!empty($receiveData)) {
            $receiveValueData = array_column($receiveData, "engineer_id");
            $receiveValueData = array_count_values($receiveValueData);
        }
        if (!empty($today)) {
            $todayValue = array_column($today, "engineer_id");
            $todayValue = array_count_values($todayValue);
        }
        # 查询所有用户
        $user = (new UserMapper())->all("id, name");
        $user = array_combine(array_column($user, "id"), array_column($user, "name"));
        foreach ($list as $k => $v) {
            $list[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $list[$k]["personnel_name"] = $user[$v["personnel_id"]]??"数据未同步";
            $list[$k]["personnel_manager_name"] = $user[$v["personnel_manager_id"]]??"数据未同步";
//            $list[$k]["personnel_train_name"] = $user[$v["personnel_train_id"]];
            $list[$k]["join_days"] = ceil((time() - $v["create_time"]) / (3600 * 24));
            $list[$k]["today_receive"] = empty($today) ? 0 : ($todayValue[$v["id"]]??0);
            $list[$k]["total_receive"] = empty($receiveData) ? 0 : ($receiveValueData[$v["id"]]??0);

            $recentTime = collect($receiveData)->where("engineer_id", "=", $v["id"])->first()["create_time"];
            # 天数差
            $freeDay = (new Carbon())->diffInDays($recentTime);
            # 小时差
            $freeHour = (new Carbon())->diffInHours($recentTime);
            if ($freeHour > 24) {
                $free = $freeDay."天".($freeHour - $freeDay * 24)."时";
            }else {
                $free = $freeHour."时";
            }
            $list[$k]["free_time"] = empty($receiveData) ? "暂无数据" : $free;
        }
        return $list;
    }


    /**
     * 工程师导出
     *
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($param) {
        $data = $this->engineer($param, true);
        foreach ($data as $k => $v) {
            $data[$k]["status"] = $v["status"] == 1 ? "启用" : "禁用";
        }
        $header = [
            ["姓名", "name"],
            ["学校", "school_name"],
            ["QQ", "contact_qq"],
            ["QQ昵称", "qq_nickname"],
            ["微信", "wechat"],
            ["微信昵称", "wechat_nickname"],
            ["电话", "contact_phone"],
            ["擅长软件", "software_name"],
            ["支付宝", "alipay"],
            ["加入天数", "join_days"],
            ["人事招聘", "personnel_name"],
            ["人事主管", "personnel_manager_name"],
            ["今日接稿", "today_receive"],
            ["累计接稿", "total_receive"],
            ["空闲期", "free_time"],
            ["状态", "status"]
        ];
        return Excel::exportData($data, $header, "工程师数据");
    }
}
