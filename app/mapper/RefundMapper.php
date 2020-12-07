<?php


namespace app\mapper;


use app\model\Refund;
use think\facade\Db;

class RefundMapper extends BaseMapper
{
    protected $model = Refund::class;

    /**
     * 退款列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundList($where) {
        # refund_view视图
        return Db::table("refund_view")
            ->where("status", "<>", 1)
            ->where($where)
            ->order("apply_time desc")
            ->select()->toArray();
    }
}
