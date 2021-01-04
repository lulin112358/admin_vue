<?php


namespace app\automation\service;


use app\BaseService;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use think\facade\Db;

class OrderService extends BaseService
{
    protected $mapper = OrdersMapper::class;

    /**
     * 自动分单
     * @param $data
     * @return array|false
     */
    public function splitOrder($data) {
        # 查询主订单创建时间
        $create_time = strtotime((new OrdersMainMapper())->findBy(["id" => $data["order_id"]], "create_time")["create_time"]);
        # 查看现有单数
        $count = $this->countBy(["main_order_id" => $data["order_id"]]);
        Db::startTrans();
        try {
            $info = $this->findBy(["main_order_id" => $data["order_id"]])->toArray();
            $orderSn = explode("-", $info["order_sn"])[0];
            if ($count == 1) {
                $res = $this->updateWhere(["main_order_id" => $data["order_id"]], ["order_sn" => $orderSn."-1", "is_auto_split" => 2]);
                if ($res === false)
                    throw new \Exception("操作失败");
            }
            $splitCount = count($data["sentence_id"])==0?1:count($data["sentence_id"]);
            if ($splitCount == 1 && $count == 1) {
                $insertData = [
                    "main_order_id" => $data["order_id"],
                    "order_sn" => $orderSn."-2",
                    "manuscript_fee" => $data["sentence_id"][0]["s_price"],
                    "check_fee" => $data["split_check_fee"]??0,
                    "delivery_time" => $info["delivery_time"],
                    "require" => $data["order_demand"],
                    "create_time" => $create_time,
                    "update_time" => $create_time,
                    "is_split" => 1,
                    "is_auto_split" => 1
                ];
                $res = $this->add($insertData);
                if (!$res)
                    throw new \Exception("操作失败！！");
            }else {
                $retData = [];
                $insertData = [];
                $all = $count + $splitCount+1;
                $k = 0;
                for ($i = $count + 1; $i < $all; $i++) {
                    $insertData[] = [
                        "main_order_id" => $data["order_id"],
                        "order_sn" => $orderSn."-".$i,
                        "manuscript_fee" => $data["sentence_id"][$k]["s_price"],
                        "check_fee" => $data["split_check_fee"]??0,
                        "delivery_time" => $info["delivery_time"],
                        "require" => $data["order_demand"],
                        "create_time" => $create_time,
                        "update_time" => $create_time,
                        "is_split" => 1,
                        "is_auto_split" => 1
                    ];
                    $item = [
                        "s_key" => $data["sentence_id"][$k]["s_key"],
                        "order_no" => $orderSn."-".$i
                    ];
                    $retData[] = $item;
                    $k++;
                }
                $res = $this->addAll($insertData);
                if (!$res)
                    throw new \Exception("操作失败!");
            }
            Db::commit();
            return $retData;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 更新订单
     * @param $param
     * @return mixed
     */
    public function updateOrder($param) {
        $updateData = [
            "engineer_id" => $param["order_engineer_id"],
            "manuscript_fee" => $param["order_price"],
            "update_time" => time()
        ];
        return $this->updateWhere(["order_sn" => $param["order_no"]], $updateData);
    }

    /**
     * 指定工程师稿费信息
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manuscript($param) {
        $data = (new OrdersMapper())->engineerManuscripts(["e.id" => $param["uid"]]);
        $retData = [
            "settlemented" => $data[0]["settlemented"],
            "total" => $data[0]["manuscript_fee"],
            "unsettlement" => $data[0]["manuscript_fee"] - $data[0]["settlemented"]
        ];
        return $retData;
    }

    /**
     * 工程师稿费详情
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manuscriptDetail($param) {
        $data = (new OrdersMapper())->engineerManuscriptsDetail(["o.engineer_id" => $param["uid"], "is_auto_split" => $param["is_auto_split"]]);
        foreach ($data as $k => $v) {
            $data[$k]["settlement_time"] = $v["settlement_time"] == 0 ? "未结算" :date("Y-m-d H:i:s", $v["settlement_time"]);
            $data[$k]["actual_delivery_time"] = date("Y-m-d H:i:s", $v["actual_delivery_time"]);
            $data[$k]["settlement_fee"] = is_null($v["settlement_fee"])?"未结算":$v["settlement_fee"];
        }
        return $data;
    }

    /**
     * 订单详情
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderInfo($param) {
        if (is_array($param["order_no"])) {
            $data = (new OrdersMapper())->orderInfo(["o.order_sn" => $param["order_no"]]);
            foreach ($data as $k => $v) {
                $data[$k]["settlement_time"] = date("Y-m-d H:i:s", $v["settlement_time"]);
                $data[$k]["settlement_status"] = $v["settlemented"]==0?"未结算":($v["settlemented"]<$v["manuscript_fee"]?"部分结算":"已结算");
            }
        }else {
            $data = (new OrdersMapper())->orderInfo(["o.order_sn" => $param["order_no"]]);
            $data = $data[0];
            $data["settlement_time"] = date("Y-m-d H:i:s", $data["settlement_time"]);
            $data["settlement_status"] = $data["settlemented"]==0?"未结算":($data["settlemented"]<$data["manuscript_fee"]?"部分结算":"已结算");
        }
        return $data;
    }
}
