<?php


namespace app\admin\service;


use app\mapper\RefundLogMapper;
use excel\Excel;

class RefundLogService extends BaseService
{
    protected $mapper = RefundLogMapper::class;

    /**
     * 退款记录
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundLogList($param) {
        $where = [];
        if (isset($param["search_key"]) && !empty($param["search_key"]))
            $where[] = ["rv.order_sn|u.name", "like", "%{$param['search_key']}%"];
        if (isset($param["refund_time"]) && !empty($param["refund_time"])) {
            $where[] = ["rl.create_time", ">=", strtotime($param["refund_time"][0])];
            $where[] = ["rl.create_time", "<=", strtotime($param["refund_time"][1])];
        }
        $data = (new RefundLogMapper())->refundLogs($where);
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["apply_time"] = date("Y-m-d H:i:s", $v["apply_time"]);
            $data[$k]["actual_refund_amount"] = floatval($v["actual_refund_amount"]);
        }
        return $data;
    }

    /**
     * 导出退款记录
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($param) {
        $data = $this->refundLogList($param);
        $header = [
            ["申请时间", "apply_time"],
            ["申请客服", "customer_name"],
            ["退款平台", "origin_name"],
            ["客户名字", "client_name"],
            ["退款金额", "actual_refund_amount"],
            ["退款原因", "refund_reason"],
            ["单号", "order_sn"],
            ["退款时间", "create_time"],
            ["退款人", "name"]
        ];
        return Excel::exportData($data, $header, "退款记录数据");
    }
}
