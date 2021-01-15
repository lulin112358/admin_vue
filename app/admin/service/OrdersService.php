<?php


namespace app\admin\service;


use Alchemy\Zippy\Zippy;
use app\mapper\AccountMapper;
use app\mapper\CategoryMapper;
use app\mapper\OrderFilesMapper;
use app\mapper\OrdersAccountMapper;
use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersFinalPaymentMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use app\mapper\OriginMapper;
use app\mapper\SchoolMapper;
use app\mapper\UserMapper;
use app\mapper\UserRoleMapper;
use Carbon\Carbon;
use excel\Excel;
use jwt\Jwt;
use PhpZip\ZipFile;
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
        6 => "已发全能",
        7 => "已发发单",
        8 => "返修中",
    ];

    # 订单状态颜色
    private $statusColor = [
        1 => "red",
        2 => "yellow",
        3 => "green",
        4 => "black",
        5 => "black",
        6 => "yellow",
        7 => "red",
        8 => "red",
    ];

    # 修改orders_main表的字段
    private $orderMain = [
        "origin_name",
        "account",
        "total_amount",
        "customer_contact",
        "customer_manager",
        "wechat",
        "cate_name"
    ];

    # 定义orders_main表字段映射关系
    private $orderMainFieldMap = [
        "origin_name" => "origin_id",
        "account" => "order_account_id",
        "wechat" => "wechat_id",
        'cate_name' => "category_id"
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
        $carbon = new Carbon();
        if ($export) {
            request()->uid = Jwt::decodeToken($params["token"])["data"]->uid;
        }
        # 获取用户角色
        $roles = (new UserRoleMapper())->columnBy(["user_id" => request()->uid], "role_id");
        # 行权限过滤
        $whereRow = [];
        $authRow = [];
        # 发单人权限
        $billerUser = [];
        if (request()->uid != 1 && !in_array(15, $roles)) {
            $authRow = row_auth();
            $authRowUserCustomer = $authRow["user_customer_id"]??[];
            array_push($authRowUserCustomer, request()->uid);
//            $authRowUserBiller = $authRow["user_biller_id"]??[];
//            array_push($authRowUserBiller, request()->uid);
            $authRowUserAlmighty = $authRow["user_almighty_id"]??[];
            array_push($authRowUserAlmighty, request()->uid);
            $billerUser = $authRow["user_biller_id"]??[];
            array_push($billerUser, request()->uid);
            if (!in_array(6, $roles) || count($roles) > 1) {
                # 获取所有网络发单人id
                $netBiller = (new UserRoleMapper())->columnBy(["role_id" => 15], "user_id");
                $billerUser = array_merge($netBiller, $billerUser);
            }
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
        $map = [];
        if (isset($params["search_fields"])) {
            foreach ($params["search_fields"] as $k => $v) {
                if (!$export) {
                    $val = json_decode($v, true);
                }else {
                    $val = explode(",", $v);
                }
                $map[$val[0]][] = $val[1];
            }
        }
        # 构造时间段查询条件
        $where = [];
        if (isset($params["date_time"]) && !empty($params["date_time"])) {
            if (strstr($params["search_order"], "create_time")) {
                $where[] = ["create_time", ">=", strtotime($params["date_time"][0])];
                $where[] = ["create_time", "<=", strtotime($params["date_time"][1])];
            }else if (strstr($params["search_order"], "delivery_time")){
                $where[] = ["delivery_time", ">=", strtotime($params["date_time"][0])];
                $where[] = ["delivery_time", "<=", strtotime($params["date_time"][1])];
            }
        }
        # 构造收款账号查询条件
        $amountAccountId = $map["amount_account_id"]??[];
        $searchKey = $params["search_key"]??"";
        unset($map["amount_account_id"]);
        foreach ($map as $k => $v) {
            $where[] = [$k, "in", $v];
        }
        # orders_view试图
        if (!$export) {
            $data = Db::table("orders_view")
                # 收款账号查询条件
                ->where(function ($query)use ($amountAccountId) {
                    if (!empty($amountAccountId)) {
                        $query->where(["deposit_amount_account_id" => $amountAccountId])
                            ->whereOr(["final_payment_amount_account_id" => $amountAccountId]);
                    }
                })
                # 行权限控制
                ->where($whereRow)
                ->where(function ($query) use ($authRow) {
                    if (request()->uid != 1) {
                        $query->where(["final_payment_amount_account_id" => ($authRow["amount_account_id"]??[])])
                        ->whereOr("final_payment_amount_account_id", null);
                    }
                })
                # 发单人权限验证/网络发单人写死的
                ->where(function ($query) use ($billerUser, $roles) {
                    if (request()->uid != 1 && !in_array(15, $roles)) {
                        $query->where(["biller_id" => $billerUser]);
                    }
                    # 网络发单人
                    if (in_array(15, $roles)) {
                        $query->where(["biller_id" => [request()->uid, 0]]);
                    }
                })
                # 模糊匹配查询条件
                ->where("manuscript_fee|biller|cate_name|check_fee|commission_ratio|customer_manager|customer_name|market_maintain|market_manager|market_user|order_sn|total_amount|customer_contact|deposit|final_payment|require|amount_account|wechat|nickname|account|origin_name|contact_qq|qq_nickname|note", "like", "%$searchKey%")
                ->where($where)
                ->fieldRaw("*, if((ifnull(deposit,0) + ifnull(final_payment,0))=0,0,(manuscript_fee / (ifnull(deposit,0) + ifnull(final_payment,0)))) as manuscript_fee_ratio")
                ->orderRaw("if(status=3, 1, 0), if(status=5, 1, 0)")
                ->order($params["search_order"])
                ->order("order_id asc")
                ->paginate(100, true)->items();
        }else {         # 导出excel不需要分页
            $data = Db::table("orders_view")->alias("ov")
                ->join(['settlement_log' => "sl"], "sl.order_id=ov.order_id", "left")
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
                # 发单人权限验证/网络发单人写死的
                ->where(function ($query) use ($billerUser, $roles) {
                    if (request()->uid != 1 && !in_array(15, $roles)) {
                        $query->where(["biller_id" => $billerUser]);
                    }
                    # 网络发单人
                    if (in_array(15, $roles)) {
                        $query->where(["biller_id" => [request()->uid, 0]]);
                    }
                })
                # 模糊匹配查询条件
                ->where("ov.manuscript_fee|ov.biller|cate_name|ov.check_fee|commission_ratio|customer_manager|customer_name|market_maintain|market_manager|market_user|ov.order_sn|total_amount|customer_contact|deposit|final_payment|ov.require|amount_account|wechat|nickname|account|origin_name|contact_qq|qq_nickname|ov.note", "like", "%$searchKey%")
                ->where($where)
                ->fieldRaw("sl.create_time as settlement_time, ov.*, if((ifnull(deposit,0) + ifnull(final_payment,0))=0,0,(ov.manuscript_fee / (ifnull(deposit,0) + ifnull(final_payment,0)))) as manuscript_fee_ratio")
                ->orderRaw("if(ov.status=3, 1, 0), if(ov.status=5, 1, 0)")
                ->order($params["search_order"])
                ->order("order_id asc")->group("ov.order_id")->select()->toArray();
        }

        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
            $data[$k]["commission_ratio"] = $v["commission_ratio"]<=1?($v["commission_ratio"] * 100)."%":$v["commission_ratio"]."元";
            $data[$k]["biller"] = is_null($v["biller"])?"暂未填写":$v["biller"];
            $data[$k]["status"] = $this->status[$v["status"]];
//            $data[$k]["manuscript_fee_ratio"] = ($v["deposit"] + $v["final_payment"])==0?0:floatval(round(($v["manuscript_fee"] / ($v["deposit"] + $v["final_payment"])) * 100, 2));
            $data[$k]["manuscript_fee_ratio"] = floatval(round($v["manuscript_fee_ratio"] * 100, 2))."%";
            $data[$k]["build_time"] = Carbon::parse($data[$k]["create_time"])->diffForHumans(Carbon::now());
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

            $billTime = Carbon::parse(date("Y-m-d H:i:s", $v["bill_time"]));
            $createTime = Carbon::parse($data[$k]["create_time"]);
            # 分钟差
            $billTimeMinutes = $createTime->diffInMinutes($billTime);
            # 小时差
            $billTimeHours = $createTime->diffInHours($billTime);
            # 天数差
            $billTimeDays = $createTime->diffInDays($billTime);
            if ($billTimeHours > 24) {
                $billTimeDiff = $billTimeDays."天".($billTimeHours - $billTimeDays * 24)."时".($billTimeMinutes - $billTimeHours * 60)."分";
            }else if ($billTimeMinutes > 60) {
                $billTimeDiff = $billTimeHours."时".($billTimeMinutes - $billTimeHours * 60)."分";
            }else{
                $billTimeDiff = $billTimeMinutes."分";
            }
            $data[$k]["bill_time"] = $v["bill_time"]==0?"未记录":$billTimeDiff;


            # TODO 此处待优化
            $time = Carbon::parse(date("Y-m-d H:i:s", $v["delivery_time"]));
            # 天数差
            $diffDay = $carbon->diffInDays($time);
            # 小时差
            $diffHour = $carbon->diffInHours($time);
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
            switch ($v["status"]) {
                case 3:
                    $diff = "已交稿";
                    $data[$k]["color"] = "black";
                    break;
                case 5:
                    $diff = "已退款";
                    $data[$k]["color"] = "black";
                    break;
                default:
                    break;
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
        # 获取用户角色
//        $roles = (new UserRoleMapper())->columnBy(["user_id" => request()->uid], "role_id");
        # 行权限过滤
//        $whereRow = [];
//        $authRow = [];
//        # 发单人权限
////        $billerUser = [];
//        if (request()->uid != 1 && !in_array(15, $roles)) {
//            $authRow = row_auth();
//            $authRowUserCustomer = $authRow["user_customer_id"]??[];
//            array_push($authRowUserCustomer, request()->uid);
////            $authRowUserBiller = $authRow["user_biller_id"]??[];
////            array_push($authRowUserBiller, request()->uid);
//            $authRowUserAlmighty = $authRow["user_almighty_id"]??[];
//            array_push($authRowUserAlmighty, request()->uid);
////            $billerUser = $authRow["user_biller_id"]??[];
////            array_push($billerUser, request()->uid);
////            $authRowUserCommissioner = $authRow["user_commissioner_id"]??[];
////            array_push($authRowUserCommissioner, request()->uid);
////            $authRowUserMaintain = $authRow["user_maintain_id"]??[];
////            array_push($authRowUserMaintain, request()->uid);
////            $authRowUserManager = $authRow["user_manager_id"]??[];
////            array_push($authRowUserManager, request()->uid);
//            $whereRow[] = ["account_id", "in", ($authRow["account_id"]??[])];
//            $whereRow[] = ["origin_id", "in", ($authRow["origin_id"]??[])];
//            $whereRow[] = ["wechat_id", "in", ($authRow["wechat_id"]??[])];
//            $whereRow[] = ["customer_id", "in", $authRowUserCustomer];
//            $whereRow[] = ["customer_manager_id", "in", $authRowUserAlmighty];
////            $whereRow[] = ["market_user_id", "in", $authRowUserCommissioner];
////            $whereRow[] = ["market_maintain_id", "in", $authRowUserMaintain];
////            $whereRow[] = ["market_manager_id", "in", $authRowUserManager];
//            $whereRow[] = ["deposit_amount_account_id", "in", ($authRow["amount_account_id"]??[])];
//        }

        $data = Db::table("orders_view")
            # 查询目前可用的记录
            ->where(["order_id" => $param["order_id"]])
            # 行权限控制
//            ->where($whereRow)
//            ->where(function ($query) use ($authRow) {
//                if (request()->uid != 1) {
//                    $query->where(["final_payment_amount_account_id" => ($authRow["amount_account_id"]??[])])
//                        ->whereOr("final_payment_amount_account_id", null);
//                }
//            })
            ->find();

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
     * @param $data
     * @return string|string[]
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
            $orderSn = $codename.substr(date("ymd"), 1).str_pad($count, 2, "0", STR_PAD_LEFT);
            # 主订单添加信息
            $orderMainData = [
                "customer_id" => request()->uid,
                "origin_id" => $data["origin_id"],
                "order_account_id" => $data["account_id"],
                "total_amount" => $data["total_amount"]??0,
                "customer_contact" => $data["customer_contact"]??'',
                "customer_manager" => $data["customer_manager"],
                "category_id" => is_array($data["cate_id"])?(count($data["cate_id"])==2?$data["cate_id"][1]:$data["cate_id"][0]):$data["cate_id"],
                "wechat_id" => $data["wechat_id"],
                "school_id" => $data["school_id"]??0,
                "degree_id" => $data["degree_id"]??0,
                "file" => "",
                "create_time" => time(),
                "update_time" => time()
            ];
            $mainRes = (new OrdersMainMapper())->add($orderMainData);
            if (!$mainRes)
                throw new \Exception("订单信息添加失败");
            # 分订单信息添加
            $orderData = [
                "main_order_id" => $mainRes->id,
                "order_sn" => $orderSn,
                "require" => $data["require"]??'',
                "note" => $data["note"]??"",
                "delivery_time" => strtotime($data["delivery_time"].":00:00"),
                "create_time" => time(),
                "update_time" => time()
            ];
            $subRes = $this->add($orderData);
            if (!$subRes)
                throw new \Exception("订单信息添加失败!");
            # 上传文档信息添加
            if(isset($data["file_path"])){
                $fileData = [];
                foreach ($data["file_path"] as $k => $v) {
                    $item = [
                        "order_id" => $subRes->id,
                        "main_order_id" => $mainRes->id,
                        "file" => $v["save_filename"],
                        "filename" => $v["filename"],
                        "user_id" => request()->uid,
                        "create_time" => time(),
                        "update_time" => time()
                    ];
                    $fileData[] = $item;
                }
                $fileRes = (new OrderFilesMapper())->addAll($fileData);
                if (!$fileRes)
                    throw new \Exception("文档添加失败");
            }

            # 收款信息添加
            $res = (new OrdersDepositMapper())->updateWhere(["main_order_id" => $mainRes->id], ["status" => 0]);
            if ($res === false)
                throw new \Exception("添加失败");
            $orderDepositData = [
                "main_order_id" => $mainRes->id,
                "change_deposit" => $data["deposit_amount"]??0,
                "deposit" => $data["deposit_amount"]??0,
                "amount_account_id" => $data["amount_account_id"],
                "payee_id" => request()->uid,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OrdersDepositMapper())->add($orderDepositData);
            if (!$res)
                throw new \Exception("添加失败!");
            Db::commit();
            # 构造剪贴板内容
            if ($data["account_id"] == $data["wechat_id"]) {
                $returnData = [
                    "content" => "http://customer.erp2020.top/customer/order?oid=".base64_encode($mainRes->id).
                        "\r\n麻烦您核实并填写下表单内容，您的订单编号为：{$orderData['order_sn']}"
                ];
            }else {
                # 获取沉淀微信
                $wechatId = (new OrdersAccountMapper())->findBy(["id" => $data["wechat_id"]], "account_id")["account_id"];
                $wechat = (new AccountMapper())->accountInfo($wechatId)["account"];
                # 获取来源
                $origin = (new OriginMapper())->findBy(["id" => $data["origin_id"]], "origin_name")["origin_name"];
                $returnData = [
                    "content" => "http://customer.erp2020.top/customer/order?oid=".base64_encode($mainRes->id).
                        "\r\n麻烦您核实并填写下表单内容，您的订单编号为：{$orderData['order_sn']}，并添加我微信: {$wechat} 
验证信息为: {$origin}-{$orderData['order_sn']}
将文件及检测报告发给我微信。"
                ];
            }
            return $returnData;
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
            # 如果更新工程师则更新发单人、发单时间和订单状态
            if ($data["field"] == "engineer_id") {
                $updateData["biller"] = request()->uid;
                $updateData["status"] = 2;
                $updateData["bill_time"] = time();
            }
            # 如果更新订单状态为已交稿则更新实际交稿时间
            if ($data["field"] == "status" && $data["value"] == 3) {
                $engineer_id = $this->findBy(["id" => $data["order_id"]], "engineer_id")["engineer_id"];
                if ($engineer_id == 0)
                    return "该订单暂未发单 不允许交稿";
                $totalAmount = (new OrdersMainMapper())->findBy(["id" => $data["main_order_id"]], "total_amount")["total_amount"];
                $deposit = (new OrdersDepositMapper())->findBy(["main_order_id" => $data["main_order_id"], "status" => 1], "deposit")["deposit"];
                $finalPayment = (new OrdersFinalPaymentMapper())->findBy(["main_order_id" => $data["main_order_id"], "status" => 1], "final_payment")["final_payment"];
                if ($totalAmount != ($deposit + $finalPayment))
                    return "尾款没有收齐 不允许交稿";
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
            $category = $category_pid==0?$category:[$category_pid, $category];
            $customer_manager = $this->maxAutoValue($data, "customer_manager");
            $schoolData = collect($data)->where("school_id", "<>", 0)->toArray();
            $degreeData = collect($data)->where("degree_id", "<>", 0)->toArray();
            $school_id = empty($schoolData) ? 0 : $this->maxAutoValue($schoolData, "school_id");
            $degree_id = empty($degreeData) ? 0 : $this->maxAutoValue($degreeData, "degree_id");

            $auto = array_column($data, "auto");
            $originData = [];
            $orderAccountData = [];
            $amountAccountData = [];
            foreach ($auto as $k => $v) {
                $autoData = explode("-", $v);
                $originData[] = $autoData[0];
                $orderAccountData[$autoData[0]][] = (int)$autoData[1];
                $amountAccountData[$autoData[0]][$autoData[1]][] = $autoData[2];
            }

            $origin = $this->maxValue($originData);
            $order_account_id = $this->maxValue($orderAccountData[$origin]);
            $amount_account_id = $this->maxValue($amountAccountData[$origin][$order_account_id]);

            $retData = compact("school_id", "degree_id", "wechat", "category", "customer_manager", "origin", "order_account_id", "amount_account_id");
            if (isset($param["field"])) {
                if ($param["field"] == "origin_id")
                    unset($retData["origin"]);
                if ($param["field"] == "order_account_id") {
                    unset($retData["origin"]);
                    unset($retData["order_account_id"]);
                }
            }
            if ($retData["school_id"] == 0) {
                unset($retData["school_id"]);
            }
            if ($retData["degree_id"] == 0) {
                unset($retData["degree_id"]);
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
            if ($splitCount == 1 && $count == 1) {
                $insertData = [
                    "main_order_id" => $data["main_order_id"],
                    "order_sn" => $orderSn."-2",
                    "manuscript_fee" => $data["manuscript_fee"]??0,
                    "check_fee" => $data["split_check_fee"]??0,
                    "delivery_time" => $info["delivery_time"],
                    "create_time" => $create_time,
                    "update_time" => $create_time,
                    "is_split" => 1
                ];
                $res = $this->add($insertData);
                if (!$res)
                    throw new \Exception("操作失败！！");
            }else {
                $insertData = [];
                $all = $count + ($data["split_count"]??1)+1;
                for ($i = $count + 1; $i < $all; $i++) {
                    $insertData[] = [
                        "main_order_id" => $data["main_order_id"],
                        "order_sn" => $orderSn."-".$i,
                        "manuscript_fee" => $data["manuscript_fee"]??0,
                        "check_fee" => $data["split_check_fee"]??0,
                        "delivery_time" => $info["delivery_time"],
                        "create_time" => $create_time,
                        "update_time" => $create_time,
                        "is_split" => 1
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
     * 确认信息
     * @param $param
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function confirmInfo($param) {
        # 获取所需信息
        $info = (new OrdersMapper())->confirmInfo(["o.id" => $param["order_id"]]);
        # 构造剪贴板内容
        if ($info["account_id"] == $info["wechat_id"]) {
            $text = "http://customer.erp2020.top/customer/order?oid=".base64_encode($param["order_id"]).
                "\r\n麻烦您核实并填写下表单内容, 您的订单编号为: {$info['order_sn']}";
        }else {
            # 获取沉淀微信
            $wechatId = (new OrdersAccountMapper())->findBy(["id" => $info["wechat_id"]], "account_id")["account_id"];
            $wechat = (new AccountMapper())->accountInfo($wechatId)["account"];
            # 获取来源
            $origin = (new OriginMapper())->findBy(["id" => $info["origin_id"]], "origin_name")["origin_name"];
            $text = "http://customer.erp2020.top/customer/order?oid=".base64_encode($param["order_id"]).
                "\r\n麻烦您核实并填写下表单内容，您的订单编号为：{$info["order_sn"]}，并添加我微信: {$wechat}
验证信息为: {$origin}-{$info['order_sn']}
将文件及检测报告发给我微信。";
        }
        return $text;
    }

    /**
     * 上传文档
     * @param $param
     * @return mixed
     */
    public function bindDoc($param) {
        $data = [];
        foreach ($param["file_path"] as $k => $v) {
            $item = [
                "order_id" => $param["order_id"],
                "main_order_id" => $param["main_order_id"],
                "file" => $v["save_filename"],
                "filename" => $v["filename"],
                "user_id" => request()->uid,
                "create_time" => time(),
                "update_time" => time()
            ];
            $data[] = $item;
        }
        return (new OrderFilesMapper())->addAll($data);
    }

    /**
     * 下载列表
     * @param $param
     * @return mixed
     */
    public function docList($param) {
        $data = (new OrderFilesMapper())->docList(["of.order_id" => $param["order_id"], "of.main_order_id" => $param["main_order_id"]]);
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
        }
        return $data;
    }

    /**
     * 下载
     * @param $param
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function downDoc($param) {
        $zip = new ZipFile();

        $files = (new OrderFilesMapper())->downDoc(["of.id" => $param["id"]]);
//        $files = (new OrdersMainMapper())->downDoc(["om.id" => $param["main_order_id"]]);
        $downFileName = implode(",", array_unique(array_column($files, "order_sn"))).".zip";
        if (file_exists(root_path()."public/storage/doczips/".$downFileName)) {
            unlink(root_path()."public/storage/doczips/".$downFileName);
        }
        foreach ($files as $k => $v) {
//            $filename = empty($v["require"])?basename($v["filename"]):$v["require"].".".explode(".", basename($v["filename"]))[1];
            $filename = $v["filename"];
            $zip->addFile(root_path()."public/storage/".$v["file"], $v["order_sn"]."/".$filename);
//            if (!empty($v["file"])) {
//                foreach (explode(",", $v["file"]) as $key => $val) {
//                    $filename = empty($v["require"])?basename($val):$v["require"].".".explode(".", basename($val))[1];
//                    $zip->addFile(root_path()."public/storage/".$val, $v["order_sn"]."/".$filename);
//                }
//            }
        }
        if (empty($zip->getListFiles())) {
            throw new \Exception("暂无文档下载");
        }
        $zip->saveAsFile(root_path()."public/storage/doczips/".$downFileName)->close();
        download_file(root_path()."public/storage/doczips/".$downFileName, $downFileName);
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
        foreach ($_data as $k => $v) {
            $_data[$k]["remain_fee"] = floatval($v["manuscript_fee"] - $v["settlemented"] - $v["deduction"]);
            if ($v["settlemented"] == 0) {
                $settlementStatus = "未结算";
            }
            if ($_data[$k]["remain_fee"] == 0) {
                $settlementStatus = "已结算";
            }
            if ($v["settlemented"] != 0 && $_data[$k]["remain_fee"] != 0) {
                $settlementStatus = "部分结算";
            }
            if ($v["manuscript_fee"] == 0) {
                $settlementStatus = '稿费为0默认已结算';
            }
            $_data[$k]["settlement_status"] = $settlementStatus;
            $_data[$k]["settlement_time"] = is_null($v["settlement_time"])?'未结算':date("Y-m-d H:i:s", $v["settlement_time"]);
        }
        $header = [
            ["创建时间", "create_time"],
            ["来源", "origin_name"],
            ["接单客服", "customer_name"],
            ["总价", "total_amount"],
            ["定金", "deposit"],
            ["尾款", "final_payment"],
            ["工程师QQ", "contact_qq"],
            ["交稿时间", "delivery_time"],
            ["稿费", "manuscript_fee"],
            ["检测费", "check_fee"],
            ["接单账号", "account"],
            ["接单昵称", "nickname"],
            ["发单人", "biller"],
            ["全能客服", "customer_manager"],
            ["订单编号", "order_sn"],
            ["结算状态", "settlement_status"],
            ["结算时间", "settlement_time"],
            ["客户联系方式", "customer_contact"],
            ["业务分支", "cate_name"],
            ["倒计时", "countdown"],
            ["备注", "note"],
            ["要求", "require"],
            ["状态", "status"],
            ["沉淀微信", "wechat"],
            ["提成比例", "commission_ratio"],
            ["市场专员", "market_user"],
            ["市场管理", "market_manager"],
            ["市场维护", "market_maintain"],
            ["工程师", "qq_nickname"],
        ];
        return Excel::exportData($_data, $header, "订单数据");
    }

    /**
     * 获取之前添加的前10学校列表
     * @return mixed
     */
    public function topSchools() {
        $mapper = new OrdersMainMapper();
        $data = $mapper->selectBy([["customer_id", "=", request()->uid], ["school_id", "<>", 0]], "school_id");
        $school_ids = array_flip(array_count_values(array_column($data, "school_id")));
        $school_ids_keys = array_keys($school_ids);
        rsort($school_ids_keys);
        $school_ids_keys = array_splice($school_ids_keys, 0, 10);
        $school_ids_arr = [];
        foreach ($school_ids_keys as $k => $v) {
            $school_ids_arr[] = $school_ids[$v];
        }
        return (new SchoolMapper())->selectBy(["id" => $school_ids_arr], "id, name");
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

    private function maxValue($data) {
        $_data = array_flip(array_count_values($data));
        $array_keys = array_keys($_data);
        rsort($array_keys);
        return $_data[$array_keys[0]];
    }
}
