<?php


namespace app\mapper;


use app\model\Account;
use think\facade\Db;

class AccountMapper extends BaseMapper
{
    protected $model = Account::class;

    /**
     * 获取账号信息
     * @param $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountInfo($data) {
        return Db::table("orders_account")->alias("oa")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->join(["account_cate" => "ac"], "ac.id=a.account_cate")
            ->where(["oa.id" => $data])
            ->field("oa.account_id, a.account, oa.id, ac.cate_name, oa.nickname, a.simple_name, a.account_cate, oa.is_wechat")
            ->find();
    }
}
