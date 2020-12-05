<?php


namespace app\admin\service;


use app\mapper\AmountAccountMapper;
use app\mapper\UserAuthRowMapper;
use think\facade\Db;

class AmountAccountService extends BaseService
{
    protected $mapper = AmountAccountMapper::class;

    /**
     * 添加收款账号
     *
     * @param $param
     * @return bool
     */
    public function addAmountAccount($param) {
        Db::startTrans();
        try {
            $res = $this->add($param);
            if (!$res)
                throw new \Exception("添加失败");
            # 添加账号可见权限
            $userAuthRowData = [
                "type" => "amount_account_id",
                "type_id" => $res->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($userAuthRowData);
            if (!$res)
                throw new \Exception("添加失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 更新收款账户
     *
     * @param $data
     * @return bool|string
     */
    public function updateAccount($data) {
        Db::startTrans();
        try {
            $res = $this->updateWhere(["id" => $data["id"]], ["status" => 0]);
            if ($res === false)
                throw new \Exception("修改失败");
            $amountAccountData = [
                "account" => $data["account"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($amountAccountData);
            if (!$res)
                throw new \Exception("修改失败啦");
            # 添加账号可见权限
            $userAuthRowData = [
                "type" => "amount_account_id",
                "type_id" => $res->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($userAuthRowData);
            if (!$res)
                throw new \Exception("添加失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }
}