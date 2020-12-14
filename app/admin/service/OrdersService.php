<?php


namespace app\admin\service;


use app\mapper\CategoryMapper;
use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use app\mapper\SchoolMapper;
use app\mapper\UserMapper;
use Carbon\Carbon;
use excel\Excel;
use jwt\Jwt;
use think\facade\Db;

class OrdersService extends BaseService
{
    protected $mapper = OrdersMapper::class;

    # 订单状态
    private $status = [
        1 => "未发出",
        2 => "已发出",
        3 => "已交稿",
        4 => "准备退款",
        5 => "已退款",
        6 => "已发全能"
    ];

    # 订单状态颜色
    private $statusColor = [
        1 => "red",
        2 => "yellow",
        3 => "green",
        4 => "black",
        5 => "black",
        6 => "yellow"
    ];

    # 修改orders_main表的字段
    private $orderMain = [
        "origin_name",
        "account",
        "total_amount",
        "customer_contact",
        "customer_manager",
        "wechat"
    ];

    # 定义orders_main表字段映射关系
    private $orderMainFieldMap = [
        "origin_name" => "origin_id",
        "account" => "order_account_id",
        "wechat" => "wechat_id"
    ];

    # 定义orders表字段映射关系
    private $orderFieldMap = [
        "contact_qq" => "engineer_id"
    ];

    # 定义autoFill字段映射关系
    private $autoFillMap = [
        "origin_id" => "om.origin_id",
        "order_account_id" => "om.order_account_id",
    ];

    /**
     * 获取所有订单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orders($params, $export = false) {
        // 设置中文
        Carbon::setLocale("zh");
        if ($export) {
            request()->uid = Jwt::decodeToken($params["token"])["data"]->uid;
        }
        # 行权限过滤
        $whereRow = [];
        $authRow = [];
        # 发单人权限
//        $billerUser = [];
        if (request()->uid != 1) {
            $authRow = row_auth();
            $authRowUserCustomer = $authRow["user_customer_id"]??[];
            array_push($authRowUserCustomer, request()->uid);
//            $authRowUserBiller = $authRow["user_biller_id"]??[];
//            array_push($authRowUserBiller, request()->uid);
            $authRowUserAlmighty = $authRow["user_almighty_id"]??[];
            array_push($authRowUserAlmighty, request()->uid);
//            $billerUser = $authRow["user_biller_id"]??[];
//            array_push($billerUser, request()->uid);
//            $authRowUserCommissioner = $authRow["user_commissioner_id"]??[];
//            array_push($authRowUserCommissioner, request()->uid);
//            $authRowUserMaintain = $authRow["user_maintain_id"]??[];
//            array_push($authRowUserMaintain, request()->uid);
//            $authRowUserManager = $authRow["user_manager_id"]??[];
//            array_push($authRowUserManager, request()->uid);
            $whereRow[] = ["account_id", "in", ($authRow["account_id"]??[])];
            $whereRow[] = ["origin_id", "in", ($authRow["origin_id"]??[])];
            $whereRow[] = ["wechat_id", "in", ($authRow["wechat_id"]??[])];
            $whereRow[] = ["customer_id", "in", $authRowUserCustomer];
            $whereRow[] = ["customer_manager_id", "in", $authRowUserAlmighty];
//            $whereRow[] = ["market_user_id", "in", $authRowUserCommissioner];
//            $whereRow[] = ["market_maintain_id", "in", $authRowUserMaintain];
//            $whereRow[] = ["market_manager_id", "in", $authRowUserManager];
            $whereRow[] = ["deposit_amount_account_id", "in", ($authRow["amount_account_id"]??[])];
        }
        # 构造字段查询条件
        $where = [];
        if (isset($params["search_fields"])) {
            foreach ($params["search_fields"] as $k => $v) {
                if (!$export) {
                    $val = json_decode($v, true);
                }else {
                    $val = explode(",", $v);
                }
                $where[$val[0]][] = $val[1];
            }
        }
        # 构造时间段查询条件
        if (isset($params["date_time"])) {
            if (strstr($params["search_order"], "create_time")) {
                $where[] = ["create_time", ">=", strtotime($params["date_time"][0])];
                $where[] = ["create_time", "<=", strtotime($params["date_time"][1])];
            }else{
                $where[] = ["delivery_time", ">=", strtotime($params["date_time"][0])];
                $where[] = ["delivery_time", "<=", strtotime($params["date_time"][1])];
            }
        }
        # 构造收款账号查询条件
        $amountAccountId = $where["amount_account_id"]??[];
        $searchKey = $params["search_key"]??"";
        unset($where["amount_account_id"]);
        # orders_view试图
        if (!$export) {
            $data = Db::table("orders_view")
                # 查询目前可用的记录
                ->where(["deposit_status" => 1])
                ->where(function ($query) {
                    $query->where(["final_payment_status" => 1])->whereOr("final_payment_status", null);
                })
                # 收款账号查询条件
                ->where(function ($query)use ($amountAccountId) {
                    if (!empty($amountAccountId)) {
                        $query->where(["deposit_amount_account_id" => $amountAccountId])
                            ->whereOr(["final_payment_amount_account_id" => $amountAccountId]);
                    }
                })
                # 行权限控制
                ->where($whereRow)
//                ->where(function ($query) use ($authRow) {
//                    if (request()->uid != 1) {
//                        $query->where(["engineer_id" => ($authRow["engineer_id"]??[])])
//                        ->whereOr("engineer_id", null);
//                    }
//                })
                ->where(function ($query) use ($authRow) {
                    if (request()->uid != 1) {
                        $query->where(["final_payment_amount_account_id" => ($authRow["amount_account_id"]??[])])
                        ->whereOr("final_payment_amount_account_id", null);
                    }
                })
                # 发单人权限验证
//                ->where(function ($query) use ($billerUser) {
//                    if (request()->uid != 1) {
//                        $query->where(["biller_id" => $billerUser])
//                            ->whereOr("biller_id", 0);
//                    }
//                })
                # 模糊匹配查询条件
                ->where("manuscript_fee|biller|cate_name|check_fee|commission_ratio|customer_manager|customer_name|market_maintain|market_manager|market_user|order_sn|total_amount|customer_contact|deposit|final_payment|require|amount_account|wechat|nickname|account|origin_name|contact_qq|qq_nickname|note", "like", "%$searchKey%")
                ->where($where)
                ->orderRaw("if(status=3, 1, 0)")
                ->order($params["search_order"])
                ->order("order_id asc")
                ->paginate(100, true)->items();
        }else {         # 导出excel不需要分页
            $data = Db::table("orders_view")
                # 查询目前可用的记录
                ->where(["deposit_status" => 1])
                ->where(function ($query) {
                    $query->where(["final_payment_status" => 1])->whereOr("final_payment_status", null);
                })
                # 收款账号查询条件
                ->where(function ($query)use ($amountAccountId) {
                    if (!empty($amountAccountId)) {
                        $query->where(["deposit_amount_account_id" => $amountAccountId])
                            ->whereOr(["final_payment_amount_account_id" => $amountAccountId]);
                    }
                })
                # 行权限控制
                ->where($whereRow)
//                ->where(function ($query) use ($authRow) {
//                    if (request()->uid != 1) {
//                        $query->where(["engineer_id" => $authRow["engineer_id"]??[]])
//                            ->whereOr("engineer_id", null);
//                    }
//                })
                ->where(function ($query) use ($authRow) {
                    if (request()->uid != 1) {
                        $query->where(["final_payment_amount_account_id" => $authRow["amount_account_id"]??[]])
                            ->whereOr("final_payment_amount_account_id", null);
                    }
                })
                # 发单人权限验证
//                ->where(function ($query) use ($billerUser) {
//                    if (request()->uid != 1) {
//                        $query->where(["biller_id" => $billerUser])
//                            ->whereOr("biller_id", 0);
//                    }
//                })
                # 模糊匹配查询条件
                ->where("manuscript_fee|biller|cate_name|check_fee|commission_ratio|customer_manager|customer_name|market_maintain|market_manager|market_user|order_sn|total_amount|customer_contact|deposit|final_payment|require|amount_account|wechat|nickname|account|origin_name|contact_qq|qq_nickname|note", "like", "%$searchKey%")
                ->where($where)
                ->orderRaw("if(status=3, 1, 0)")
                ->order($params["search_order"])
                ->order("order_id asc")->select()->toArray();
        }

        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
            $data[$k]["commission_ratio"] = $v["commission_ratio"]<=1?($v["commission_ratio"] * 100)."%":$v["commission_ratio"]."元";
            $data[$k]["biller"] = is_null($v["biller"])?"暂未填写":$v["biller"];
            $data[$k]["status"] = $this->status[$v["status"]];
            # 保留有效位数
            $data[$k]["total_amount"] = floatval($v["total_amount"]);
            $data[$k]["total_fee"] = floatval($v["total_amount"]);
            $data[$k]["deposit"] = floatval($v["deposit"]);
            $data[$k]["refund_amount"] = is_null($v["refund_amount"])?"未退款":floatval($v["refund_amount"]);
            $data[$k]["final_payment"] = floatval($v["final_payment"]);
            $data[$k]["manuscript_fee"] = floatval($v["manuscript_fee"]);
            $data[$k]["check_fee"] = floatval($v["check_fee"]);
            # 消除分单后的总价/定金/尾款显示
            $order_sn = explode("-", $v["order_sn"]);
            if (count($order_sn) > 1) {
                if ($order_sn[1] != "1") {
                    $data[$k]["total_amount"] = "";
                    $data[$k]["deposit"] = "";
                    $data[$k]["final_payment"] = "";
                }
            }

            # TODO 此处待优化
            $time = Carbon::parse(date("Y-m-d H:i:s", $v["delivery_time"]));
            # 天数差
            $diffDay = (new Carbon())->diffInDays($time);
            # 小时差
            $diffHour = (new Carbon())->diffInHours($time);
            if ($diffHour > 24) {
                $diff = $diffDay."天".($diffHour - $diffDay * 24)."时";
            }else{
                $diff = $diffHour."时";
            }
            if (!$time->gt(Carbon::now())) {
                $diff = "超".$diff;
                $data[$k]["color"] = "red";
            }else{
                $rate = ($v["delivery_time"] - time()) / ($v["delivery_time"] - $v["create_time"]);
                if ($rate <= 0.3)
                    $data[$k]["color"] = "red";
                if ($rate <= 0.5 && $rate > 0.3)
                    $data[$k]["color"] = "yellow";
                if ($rate > 0.5)
                    $data[$k]["color"] = "green";
            }
            $data[$k]["status_color"] = $this->statusColor[$v["status"]];
            if ($v["status"] == 3) {
                $diff = "已交稿";
                $data[$k]["color"] = "black";
            }
            $data[$k]["countdown"] = $diff;
        }

        return $data;
    }


    /**
     * 获取指定订单记录
     *
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function order($param) {
        // 设置中文
        Carbon::setLocale("zh");

        # 行权限过滤
        $whereRow = [];
        $authRow = [];
        # 发单人权限
//        $billerUser = [];
        if (request()->uid != 1) {
            $authRow = row_auth();
            $authRowUserCustomer = $authRow["user_customer_id"]??[];
            array_push($authRowUserCustomer, request()->uid);
//            $authRowUserBiller = $authRow["user_biller_id"]??[];
//            array_push($authRowUserBiller, request()->uid);
            $authRowUserAlmighty = $authRow["user_almighty_id"]??[];
            array_push($authRowUserAlmighty, request()->uid);
//            $billerUser = $authRow["user_biller_id"]??[];
//            array_push($billerUser, request()->uid);
//            $authRowUserCommissioner = $authRow["user_commissioner_id"]??[];
//            array_push($authRowUserCommissioner, request()->uid);
//            $authRowUserMaintain = $authRow["user_maintain_id"]??[];
//            array_push($authRowUserMaintain, request()->uid);
//            $authRowUserManager = $authRow["user_manager_id"]??[];
//            array_push($authRowUserManager, request()->uid);
            $whereRow[] = ["account_id", "in", ($authRow["account_id"]??[])];
            $whereRow[] = ["origin_id", "in", ($authRow["origin_id"]??[])];
            $whereRow[] = ["wechat_id", "in", ($authRow["wechat_id"]??[])];
            $whereRow[] = ["customer_id", "in", $authRowUserCustomer];
            $whereRow[] = ["customer_manager_id", "in", $authRowUserAlmighty];
//            $whereRow[] = ["market_user_id", "in", $authRowUserCommissioner];
//            $whereRow[] = ["market_maintain_id", "in", $authRowUserMaintain];
//            $whereRow[] = ["market_manager_id", "in", $authRowUserManager];
            $whereRow[] = ["deposit_amount_account_id", "in", ($authRow["amount_account_id"]??[])];
        }

        $data = Db::table("orders_view")
            # 查询目前可用的记录
            ->where(["deposit_status" => 1, "order_id" => $param["order_id"]])
            ->where(function ($query) {
                $query->where(["final_payment_status" => 1])->whereOr("final_payment_status", null);
            })
            # 行权限控制
            ->where($whereRow)
//            ->where(function ($query) use ($authRow) {
//                if (request()->uid != 1) {
//                    $query->where(["engineer_id" => ($authRow["engineer_id"]??[])])
//                        ->whereOr("engineer_id", null);
//                }
//            })
                # 发单人权限
//            ->where(function ($query) use ($billerUser) {
//                if (request()->uid != 1) {
//                    $query->where(["biller_id" => $billerUser])
//                        ->whereOr("biller_id", 0);
//                }
//            })
            ->where(function ($query) use ($authRow) {
                if (request()->uid != 1) {
                    $query->where(["final_payment_amount_account_id" => ($authRow["amount_account_id"]??[])])
                        ->whereOr("final_payment_amount_account_id", null);
                }
            })->find();

        $status = $data["status"];
        $delivery_time = $data["delivery_time"];
        $create_time = $data["create_time"];
        $data["create_time"] = date("Y-m-d H:i:s", $data["create_time"]);
        $data["delivery_time"] = date("Y-m-d H", $data["delivery_time"]);
        $data["commission_ratio"] = $data["commission_ratio"]."%";
        $data["biller"] = is_null($data["biller"])?"暂未填写":$data["biller"];
        $data["status"] = $this->status[$data["status"]];
        $data["file"] = config("app.down_url").$data["file"];
        # 保留有效位数
        $data["total_amount"] = floatval($data["total_amount"]);
        $data["total_fee"] = floatval($data["total_amount"]);
        $data["deposit"] = floatval($data["deposit"]);
        $data["final_payment"] = floatval($data["final_payment"]);
        $data["manuscript_fee"] = floatval($data["manuscript_fee"]);
        $data["check_fee"] = floatval($data["check_fee"]);
        # 消除分单后的总价/定金/尾款显示
        $order_sn = explode("-", $data["order_sn"]);
        if (count($order_sn) > 1) {
            if ($order_sn[1] != "1") {
                $data["total_amount"] = "";
                $data["deposit"] = "";
                $data["final_payment"] = "";
            }
        }

        # TODO 此处待优化
        $time = Carbon::parse($data["delivery_time"].":00:00");
        # 天数差
        $diffDay = (new Carbon())->diffInDays($time);
        # 小时差
        $diffHour = (new Carbon())->diffInHours($time);
        if ($diffHour > 24) {
            $diff = $diffDay."天".($diffHour - $diffDay * 24)."时";
        }else{
            $diff = $diffHour."时";
        }
        if (!$time->gt(Carbon::now())) {
            $diff = "超".$diff;
            $data["color"] = "red";
        }else{
            $rate = ($delivery_time - time()) / ($delivery_time - $create_time);
            if ($rate <= 0.3)
                $data["color"] = "red";
            if ($rate <= 0.5 && $rate > 0.3)
                $data["color"] = "yellow";
            if ($rate > 0.5)
                $data["color"] = "green";
        }
        $data["status_color"] = $this->statusColor[$status];
        if ($data["status"] == 3) {
            $diff = "已交稿";
            $data["color"] = "black";
        }
        $data["countdown"] = $diff;
        return $data;
    }

    /**
     * 添加订单
     *
     * @param $data
     * @return bool|string
     */
    public function addOrder($data) {
        Db::startTrans();
        try {
            # 查询用户代号
            $codename = (new UserMapper())->findBy(["id" => request()->uid], "codename")["codename"];
            # 查询今日接单数量
            $count = (new OrdersMainMapper())->countBy([
                ["customer_id", "=", request()->uid],
                ["create_time", ">=", strtotime(date("Y-m-d"))],
                ["create_time", "<=", time()]
            ]) + 1;
            # 主订单添加信息
            $orderMainData = [
                "customer_id" => request()->uid,
                "origin_id" => $data["origin_id"],
                "order_account_id" => $data["account_id"],
                "total_amount" => $data["total_amount"]??0,
                "customer_contact" => $data["customer_contact"]??'',
                "customer_manager" => $data["customer_manager"],
                "category_id" => count($data["cate_id"])==2?$data["cate_id"][1]:$data["cate_id"][0],
                "wechat_id" => $data["wechat_id"],
                "file" => $data["file"]??"",
                "create_time" => time(),
                "update_time" => time()
            ];
            $mainRes = (new OrdersMainMapper())->add($orderMainData);
            if (!$mainRes)
                throw new \Exception("订单信息添加失败");
            # 分订单信息添加
            $orderData = [
                "main_order_id" => $mainRes->id,
                "order_sn" => $codename.substr(date("ymd"), 1).str_pad($count, 2, "0", STR_PAD_LEFT),
                "require" => $data["require"]??'',
                "note" => $data["note"]??"",
                "delivery_time" => strtotime($data["delivery_time"].":00:00"),
                "create_time" => time(),
                "update_time" => time()
            ];
            $subRes = $this->add($orderData);
            if (!$subRes)
                throw new \Exception("订单信息添加失败!");
            # 收款信息添加
            $res = (new OrdersDepositMapper())->updateWhere(["main_order_id" => $mainRes->id], ["status" => 0]);
            if ($res === false)
                throw new \Exception("添加失败");
            $orderDepositData = [
                "main_order_id" => $mainRes->id,
                "change_deposit" => $data["deposit_amount"],
                "deposit" => $data["deposit_amount"]??0,
                "amount_account_id" => $data["amount_account_id"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OrdersDepositMapper())->add($orderDepositData);
            if (!$res)
                throw new \Exception("添加失败!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }


    /**
     * 更新订单信息
     *
     * @param $data
     * @return mixed
     */
    public function updateOrder($data) {
        # 修改尾款
        if ($data["field"] == "final_payment") {
            return (new OrdersFinalPaymentService())->updateFinalPayment($data);
        }
        # 修改定金
        if ($data["field"] == "deposit") {
            return (new OrdersDepositService())->updateDeposit($data);
        }
        if (in_array($data["field"], $this->orderMain)) {
            # 修改orders_main表信息
            # 根据字段映射关系修改字段名
            if (in_array($data["field"], array_keys($this->orderMainFieldMap)))
                $data["field"] = $this->orderMainFieldMap[$data["field"]];
            # 更新数据库
            $updateData = [
                "id" => $data["main_order_id"],
                $data["field"] => $data["value"]
            ];
            return (new OrdersMainMapper())->updateBy($updateData);
        }else {
            # 修改orders表信息
            # 根据字段映射关系修改字段名
            if (in_array($data["field"], array_keys($this->orderFieldMap)))
                $data["field"] = $this->orderFieldMap[$data["field"]];
            # 更新数据库
            # 时间格式特殊处理
            if ($data["field"] == "delivery_time")
                $data["value"] = strtotime($data["value"].":00:00");
            $updateData = [
                "id" => $data["order_id"],
                $data["field"] => $data["value"]
            ];
            # 如果更新工程师则更新发单人和订单状态
            if ($data["field"] == "engineer_id") {
                $updateData["biller"] = request()->uid;
                $updateData["status"] = 2;
            }
            # 如果更新订单状态为已交稿则更新实际交稿时间
            if ($data["field"] == "status" && $data["value"] == 3) {
                $engineer_id = $this->findBy(["id" => $data["order_id"]], "engineer_id")["engineer_id"];
                if ($engineer_id == 0)
                    return "该订单暂未发单 不允许交稿";
                $updateData["actual_delivery_time"] = time();
            }
            return (new OrdersMapper())->updateBy($updateData);
        }
    }


    /**
     * 删除订单
     * @param $param
     * @return bool|\Exception
     */
    public function deleteOrder($param) {
        Db::startTrans();
        try {
            $info = $this->findBy($param, "main_order_id, status");
            if ($info["status"] != 1)
                throw new \Exception("该订单不允许删除");
            $order_main_id = $info["main_order_id"];
            $res = $this->deleteBy($param);
            if ($res === false)
                throw new \Exception("操作失败");
            $exits = $this->countBy(["main_order_id" => $order_main_id]);
            if ($exits <= 0) {
                $res = (new OrdersMainMapper())->deleteBy(["id" => $order_main_id]);
                if ($res === false)
                    throw new \Exception("操作失败!");
            }
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }

    /**
     * 自动填充
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function autoFill($param) {
        $where = [];
        if (!empty($param)) {
            $where = [
                $this->autoFillMap[$param["field"]] => $param["value"]
            ];
        }
        $data = (new OrdersMainMapper())->autoFillData($where);

        if (!empty($data)) {
            $wechat = $this->maxAutoValue($data, "wechat_id");
            $category = $this->maxAutoValue($data, "category_id");
            $category_pid = (new CategoryMapper())->findBy(["id" => $category], "pid")["pid"];
            $category = [$category_pid, $category];
            $customer_manager = $this->maxAutoValue($data, "customer_manager");
            $auto = $this->maxAutoValue($data, "auto");
            $autoData = explode("-", $auto);
            $origin = $autoData[0];
            $order_account_id = (int)$autoData[1];
            $amount_account_id = $autoData[2];

            $retData = compact("wechat", "category", "customer_manager", "origin", "order_account_id", "amount_account_id");
            if (isset($param["field"])) {
                if ($param["field"] == "origin_id")
                    unset($retData["origin"]);
                if ($param["field"] == "order_account_id") {
                    unset($retData["origin"]);
                    unset($retData["order_account_id"]);
                }
            }
            return $retData;
        }else{
            return [];
        }
    }


    /**
     * 分单
     * @param $data
     * @return bool
     */
    public function splitOrder($data) {
        # 查询主订单创建时间
        $create_time = strtotime((new OrdersMainMapper())->findBy(["id" => $data["main_order_id"]], "create_time")["create_time"]);
        # 查看现有单数
        $count = $this->countBy(["main_order_id" => $data["main_order_id"]]);
        Db::startTrans();
        try {
            $info = $this->findBy(["main_order_id" => $data["main_order_id"]])->toArray();
            $orderSn = explode("-", $info["order_sn"])[0];
            if ($count == 1) {
                $res = $this->updateWhere(["main_order_id" => $data["main_order_id"]], ["order_sn" => $orderSn."-1"]);
                if ($res === false)
                    throw new \Exception("操作失败");
            }
            $splitCount = $data["split_count"]??1;
            if ($splitCount == 1) {
                $insertData = [
                    "main_order_id" => $data["main_order_id"],
                    "order_sn" => $orderSn."-2",
                    "manuscript_fee" => $data["manuscript_fee"]??0,
                    "check_fee" => $data["split_check_fee"]??0,
                    "delivery_time" => $info["delivery_time"],
                    "create_time" => $create_time,
                    "update_time" => $create_time
                ];
                $res = $this->add($insertData);
                if (!$res)
                    throw new \Exception("操作失败！！");
            }else {
                $insertData = [];
                $all = $count + ($data["split_count"]??1);
                for ($i = $count + 1; $i < $all; $i++) {
                    $insertData[] = [
                        "main_order_id" => $data["main_order_id"],
                        "order_sn" => $orderSn."-".$i,
                        "manuscript_fee" => $data["manuscript_fee"]??0,
                        "check_fee" => $data["split_check_fee"]??0,
                        "delivery_time" => $info["delivery_time"],
                        "create_time" => $create_time,
                        "update_time" => $create_time
                    ];
                }
                $res = $this->addAll($insertData);
                if (!$res)
                    throw new \Exception("操作失败!");
            }
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }


    /**
     * 导出订单数据
     *
     * @param $data
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($data) {
        $_data = $this->orders($data, true);
        $header = [
            ["接单客服", "customer_name"],
            ["订单编号", "order_sn"],
            ["总价", "total_amount"],
            ["客户联系方式", "customer_contact"],
            ["业务分支", "cate_name"],
            ["创建时间", "create_time"],
            ["倒计时", "countdown"],
            ["检测费", "check_fee"],
            ["稿费", "manuscript_fee"],
            ["定金", "deposit"],
            ["尾款", "final_payment"],
            ["备注", "note"],
            ["要求", "require"],
            ["状态", "status"],
            ["客服主管", "customer_manager"],
            ["发单人", "biller"],
            ["沉淀微信", "wechat"],
            ["来源", "origin_name"],
            ["接单账号", "account"],
            ["接单昵称", "nickname"],
            ["提成比例", "commission_ratio"],
            ["市场专员", "market_user"],
            ["市场管理", "market_manager"],
            ["市场维护", "market_maintain"],
            ["交稿时间", "delivery_time"],
            ["工程师QQ", "contact_qq"],
            ["工程师", "qq_nickname"],
        ];
        return Excel::exportData($_data, $header, "订单数据");
    }

    /**
     * 获取之前添加的前10学校列表
     * @return mixed
     */
    public function topSchools() {
        $data = (new OrdersMainMapper())->selectBy(["customer_id" => request()->uid], "school_id");
        $school_ids = array_keys(array_count_values(array_column($data, "school_id")));
        rsort($school_ids);
        array_splice($school_ids, 0, 10);
        return (new SchoolMapper())->selectBy(["id" => $school_ids], "id, name");
    }


    /**
     * 获取出现次数最多的可能值
     *
     * @param $data
     * @param $key
     * @return mixed
     */
    private function maxAutoValue($data, $key) {
        $_data = array_flip(array_count_values(array_column($data, $key)));
        $array_keys = array_keys($_data);
        rsort($array_keys);
        return $_data[$array_keys[0]];
    }
}
