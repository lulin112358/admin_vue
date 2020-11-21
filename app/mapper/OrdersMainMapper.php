<?php


namespace app\mapper;


use app\model\OrdersMain;
use think\facade\Db;

class OrdersMainMapper extends BaseMapper
{
    protected $model = OrdersMain::class;

    /**
     * 填写主订单的主要信息
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function mainOrders($limit) {
        return OrdersMain::with(["deposits" => function($query) {
            return $query->order("create_time asc")->limit(1);
        }])->where(["customer_id" => request()->uid])
            ->limit($limit)
            ->select()->toArray();
    }

    /**
     * 自动填充数据
     *
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function autoFillData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders_deposit" => "od"], "om.id=od.main_order_id")
            ->where(["om.customer_id" => request()->uid])
            ->where($where)
            ->field("od.main_order_id, od.create_time, om.origin_id, om.order_account_id, om.customer_manager, om.id, 
            concat(om.origin_id, '-', om.order_account_id, '-', od.amount_account_id) as auto,
             om.category_id, om.wechat_id, od.amount_account_id")
            ->order("od.create_time asc")
            ->group("om.id")
            ->limit(50)
            ->select()->toArray();
    }
}
