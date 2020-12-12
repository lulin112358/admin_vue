<?php


namespace app\mapper;


use app\model\Wechat;
use think\facade\Db;

class WechatMapper extends BaseMapper
{
    protected $model = Wechat::class;

    /**
     * 获取沉淀微信列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechats() {
        return Db::table("orders_account")->alias("oa")
            ->join(["account" => "a"], "oa.account_id=a.id")
            ->where(["oa.status" => 1, "oa.is_wechat" => 1])
            ->field("oa.id, oa.nickname as wechat, a.account as wechat_id, oa.create_time, oa.update_time")
            ->order("oa.id desc")->select()->toArray();
    }
}
