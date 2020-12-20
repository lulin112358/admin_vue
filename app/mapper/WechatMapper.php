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
        return Db::table("account")->alias("a")
            ->join(["orders_account" => "oa"], "oa.account_id=a.id")
            ->where(["oa.status" => 1, "a.is_wechat" => 1, "a.status" => 1])
            ->field("a.id, oa.nickname as wechat, a.account as wechat_id, oa.create_time, oa.update_time")
            ->order("a.id desc")->select()->toArray();
    }
}
