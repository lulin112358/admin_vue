<?php


namespace app\admin\service;


use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use app\mapper\UserMapper;
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

    /**
     * 获取所有订单
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orders() {
        # orders_view试图
        $data = Db::table("orders_view")->select()->toArray();
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
            $data[$k]["market_user"] = $user[$v["market_user"]];
            $data[$k]["biller"] = $v["biller"]==0?"暂未填写":$user[$v["biller"]];
            $data[$k]["status"] = $this->status[$v["status"]];
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
            # 主订单添加信息
            $orderMainData = [
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
                "order_sn" => "测试订单号",
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
}
