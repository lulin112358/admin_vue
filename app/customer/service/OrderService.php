<?php


namespace app\customer\service;


use app\BaseService;
use app\mapper\AmountAccountMapper;
use app\mapper\CategoryMapper;
use app\mapper\OrdersDepositMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use think\facade\Db;

class OrderService extends BaseService
{
    private $logoMap = [
        41 => "/static/images/paperok.png",
        42 => "/static/images/lx.png",
        43 => "/static/images/lwg.png",
    ];

    /**
     * 订单信息
     * @param $param
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderInfo($param) {
        $main_order_id = isset($param["oid"]) ? base64_decode($param["oid"]) : 0;
        $data = Db::table("orders_view")->where(["main_order_id" => $main_order_id])
            ->field("order_sn, origin_name, account, customer_contact, delivery_time, total_amount, deposit,
             deposit_amount_account_id, require, customer_contact, main_order_id, category_id, origin_id")
            ->find();
        $data["delivery_time"] = date("Y-m-d H", $data["delivery_time"]);
        # 查询收款账号
        $amountAccount = (new AmountAccountMapper())->findBy(["id" => $data["deposit_amount_account_id"]], "account")["account"];
        # 查询分类提示语
        $placeholder = (new CategoryMapper())->placeholder(["id" => $data["category_id"]]);
        $data["placeholder"] = $placeholder;
        $data["deposit_amount_account"] = $amountAccount;
        $data["logo"] = $this->logoMap[$data["origin_id"]]??"";
        return $data;
    }


    /**
     * 客户确认订单更新订单信息
     * @param $param
     * @return bool
     */
    public function updateOrder($param) {
        Db::startTrans();
        try {
            $data = [
//                "total_amount" => $param["total_amount"],
                "customer_contact" => $param["customer_contact"]
            ];
            $main_order_id = base64_decode($param["main_order_id"]);
            $res = (new OrdersMainMapper())->updateWhere(["id" => $main_order_id], $data);
            if ($res === false)
                throw new \Exception("提交失败");

            $data = [
                "require" => $param["require"]
            ];
            $res = (new OrdersMapper())->updateWhere(["main_order_id" => $main_order_id], $data);
            if ($res === false)
                throw new \Exception("提交失败!");

//            $data = [
//                "change_deposit" => $param["deposit"],
//                "deposit" => $param["deposit"]
//            ];
//            $res = (new OrdersDepositMapper())->updateWhere(["main_order_id" => $main_order_id], $data);
//            if ($res === false)
//                throw new \Exception("提交失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
