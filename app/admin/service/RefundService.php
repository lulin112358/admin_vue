<?php


namespace app\admin\service;


use app\mapper\OrdersMapper;
use app\mapper\RefundLogMapper;
use app\mapper\RefundMapper;
use excel\Excel;
use think\facade\Db;

class RefundService extends BaseService
{
    protected $mapper = RefundMapper::class;

    private $status = [
        0 => "未退款",
        1 => "已退款",
        2 => "部分退款"
    ];

    /**
     * 退款
     * @param $param
     * @return mixed
     */
    public function refund($param) {
        $param["customer_id"] = request()->uid;
        $param["apply_time"] = time();
        Db::startTrans();
        try {
            $res = $this->add($param);
            if (!$res)
                throw new \Exception("操作失败");
            $res = (new OrdersMapper())->updateWhere(["main_order_id" => $param["order_main_id"]], ["status" => 4]);
            if ($res === false)
                throw new \Exception("操作失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 退款列表
     * @param $param
     * @return array
     */
    public function refundList($param) {
        $where = [];
        if (isset($param["search_key"]) && !empty($param["search_key"]))
            $where[] = ["customer_name|origin_name|order_sn|client_alipay|client_name|refund_reason", "like", "%{$param['search_key']}%"];
//        if (isset($param["status"]) && !empty($param["status"]))
//            $where[] = ["status", "=", $param["status"]];
        if (isset($param["apply_time"]) && !empty($param["apply_time"])) {
            $where[] = ["apply_time", ">=", strtotime($param["apply_time"][0])];
            $where[] = ["apply_time", "<=", strtotime($param["apply_time"][1])];
        }
        $data = (new RefundMapper())->refundList($where);
        foreach ($data as $k => $v) {
            $data[$k]["refund_amount"] = floatval($v["refund_amount"]);
            $data[$k]["already_refund_amount"] = floatval($v["already_refund_amount"]);
            $data[$k]["apply_time"] = date("Y-m-d H:i:s", $v["apply_time"]);
            $data[$k]["refund_time"] = $v["refund_time"] == 0 ? "暂未退款" : date("Y-m-d H:i:s", $v["refund_time"]);
            $data[$k]["status"] = $this->status[$v["status"]];
            $data[$k]["color"] = $v["status"] == 0 ? "red" : ($v["status"] == 2 ? "yellow" : "green");
        }
        return $data;
    }


    /**
     * 退款操作
     * @param $param
     * @return bool|string
     */
    public function refundHandle($param) {
        Db::startTrans();
        try {
            # 查询退款订单数据
            $info = (new RefundMapper())->findBy(["id" => $param["id"]]);
            if ($info["already_refund_amount"] + $param["refund_amount"] > $info["refund_amount"])
                throw new \Exception("超出退款金额 请检查");
            # 修改退款表
            $updateData = [
                "already_refund_amount" => $info["already_refund_amount"] + $param["refund_amount"],
                "refund_time" => time(),
                "status" => $info["already_refund_amount"] + $param["refund_amount"] < $info["refund_amount"] ? 2 : 1
            ];
            $res = (new RefundMapper())->updateWhere(["id" => $param["id"]], $updateData);
            if ($res === false)
                throw new \Exception("操作失败");

            # 新增退款记录
            $addData = [
                "refund_id" => $info["id"],
                "actual_refund_amount" => $param["refund_amount"],
                "refund_user" => request()->uid,
                "create_time" => time()
            ];
            $res = (new RefundLogMapper())->add($addData);
            if (!$res)
                throw new \Exception("操作失败啦");

            # 修改订单状态为已退款
            $res = (new OrdersMapper())->updateWhere(["main_order_id" => $info["order_main_id"]], ["status" => 5]);
            if ($res === false)
                throw new \Exception("操作失败!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }

    /**
     * 导出退款列表
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export($param) {
        $data = $this->refundList($param);
        $header = [
            ["申请时间", "apply_time"],
            ["申请客服", "customer_name"],
            ["退款平台", "origin_name"],
            ["客户支付宝", "client_alipay"],
            ["客户微信", "client_wechat"],
            ["客户名字", "client_name"],
            ["退款金额", "refund_amount"],
            ["已退款金额", "already_refund_amount"],
            ["退款原因", "refund_reason"],
            ["单号", "order_sn"],
            ["是否退款", "status"],
            ["退款时间", "refund_time"]
        ];
        return Excel::exportData($data, $header, "退款数据");
    }
}
