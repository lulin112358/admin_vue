<?php


namespace app\admin\service;


use app\mapper\AmountAccountMapper;
use think\facade\Db;

class AmountAccountService extends BaseService
{
    protected $mapper = AmountAccountMapper::class;

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
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }
}
