<?php


namespace app\admin\service;


use app\mapper\CategoryMapper;
use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use app\mapper\UserMapper;
use Carbon\Carbon;
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
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orders() {
        // 设置中文
        Carbon::setLocale("zh");
        # orders_view试图
        $data = Db::table("orders_view")->order("order_id desc")->select()->toArray();
        # 查询所有用户
        $user = (new UserMapper())->all("id, name");
        $user = array_combine(array_column($user, "id"), array_column($user, "name"));
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["delivery_time"] = date("Y-m-d H:i:s", $v["delivery_time"]);
            $data[$k]["commission_ratio"] = $v["commission_ratio"]."%";
            $data[$k]["customer_manager"] = $user[$v["customer_manager"]];
            $data[$k]["market_maintain"] = $user[$v["market_maintain"]];
            $data[$k]["market_manager"] = $user[$v["market_manager"]];
            $data[$k]["customer_id"] = $user[$v["customer_id"]];
            $data[$k]["market_user"] = $user[$v["market_user"]];
            $data[$k]["biller"] = $v["biller"]==0?"暂未填写":$user[$v["biller"]];
            $data[$k]["status"] = $this->status[$v["status"]];

            # TODO 此处待优化
            $time = Carbon::parse(date("Y-m-d H:i:s", $v["delivery_time"]));
            # 天数差
            $diffDay = (new Carbon())->diffInDays($time);
            # 小时差
            $diffHour = (new Carbon())->diffInHours($time);
            if ($diffHour > 24) {
                $diff = $diffDay."天".($diffHour - $diffDay * 24)."时";
                if (!$time->gt(Carbon::now())) {
                    $diff = "已超".$diff;
                    $data[$k]["color"] = "red";
                }else{
                    if ($v["status"] == 2 || $v["status"] == 1)
                        $data[$k]["color"] = "blue";
                }
            }else{
                $diff = $diffHour."时";
                if (!$time->gt(Carbon::now())) {
                    $diff = "已超".$diff;
                    $data[$k]["color"] = "red";
                }else{
                    if ($diffHour > 0 && $diffHour <= 6 && ($v["status"] == 1 || $v["status"] == 2)) {
                        $data[$k]["color"] = "red";
                    }
                    if ($diffHour > 6 && $diffHour <= 12 && ($v["status"] == 1 || $v["status"] == 2)) {
                        $data[$k]["color"] = "yellow";
                    }
                    if ($diffHour > 12 && ($v["status"] == 1 || $v["status"] == 2)) {
                        $data[$k]["color"] = "blue";
                    }
                }
            }
            if ($v["status"] == 3) {
                $diff = "已交稿";
                $data[$k]["color"] = "green";
            }
            $data[$k]["countdown"] = $diff;
        }
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
                "total_amount" => $data["total_amount"],
                "customer_contact" => $data["customer_contact"],
                "customer_manager" => $data["customer_manager"],
                "category_id" => $data["cate_id"][1],
                "wechat_id" => $data["wechat_id"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $mainRes = (new OrdersMainMapper())->add($orderMainData);
            if (!$mainRes)
                throw new \Exception("订单信息添加失败");
            # 分订单信息添加
            $orderData = [
                "main_order_id" => $mainRes->id,
                "order_sn" => $codename.date("Ymd").str_pad($count, 2, "0", STR_PAD_LEFT),
                "require" => $data["require"],
                "note" => $data["note"],
                "delivery_time" => strtotime($data["delivery_time"]),
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
                "deposit" => $data["deposit_amount"],
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
                $data["value"] = strtotime($data["value"]);
            $updateData = [
                "id" => $data["order_id"],
                $data["field"] => $data["value"]
            ];
            return (new OrdersMapper())->updateBy($updateData);
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
            $order_account_id = $autoData[1];
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
        # 查看现有单数
        $count = $this->countBy(["main_order_id" => $data["main_order_id"]]);
        Db::startTrans();
        try {
            $orderSn = $this->findBy(["main_order_id" => $data["main_order_id"]], "order_sn")["order_sn"];
            $orderSn = explode("-", $orderSn)[0];
            if ($count == 1) {
                $res = $this->updateWhere(["main_order_id" => $data["main_order_id"]], ["order_sn" => $orderSn."-1"]);
                if ($res === false)
                    throw new \Exception("操作失败");
            }
            $insertData = [];
            $all = $count + ($data["split_count"]??1);
            for ($i = $count + 1; $i <= $all; $i++) {
                $insertData[] = [
                    "main_order_id" => $data["main_order_id"],
                    "order_sn" => $orderSn."-".$i,
                    "manuscript_fee" => $data["manuscript_fee"]??0,
                    "check_fee" => $data["split_check_fee"]??0,
                    "create_time" => time(),
                    "update_time" => time()
                ];
            }
            $res = $this->addAll($insertData);
            if (!$res)
                throw new \Exception("操作失败!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
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
