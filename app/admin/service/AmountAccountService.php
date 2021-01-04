<?php


namespace app\admin\service;


use app\mapper\AmountAccountMapper;
use app\mapper\RoleAuthRowMapper;
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
            $res1 = $this->add($param);
            if (!$res1)
                throw new \Exception("添加失败");
            # 添加账号可见权限
            $userAuthRowData = [
                "type" => "amount_account_id",
                "type_id" => $res1->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($userAuthRowData);
            if (!$res)
                throw new \Exception("添加失败啦");
            # 管理层赋权
            $insData = [
                "type" => "amount_account_id",
                "type_id" => $res1->id,
                "role_id" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new RoleAuthRowMapper())->add($insData);
            if (!$res)
                throw new \Exception("添加失败!!");
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
            $res = $this->updateWhere(["id" => $data["id"]], ["account" => $data["account"], "update_time" => time()]);
            if ($res === false)
                throw new \Exception("修改失败");
            # 添加账号可见权限
//            $userAuthRowData = [
//                "type" => "amount_account_id",
//                "type_id" => $res->id,
//                "user_id" => request()->uid,
//                "status" => 1,
//                "create_time" => time(),
//                "update_time" => time()
//            ];
//            $res = (new UserAuthRowMapper())->add($userAuthRowData);
//            if (!$res)
//                throw new \Exception("添加失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }


    /**
     * 收款账号排序列表
     * @return array
     */
    public function accountSort() {
        $list = $this->selectBy(["status" => 1], "id, account");
        $sort = (new AmountAccountMapper())->accountSort();
        $sort = array_count_values($sort);
        arsort($sort);
        $retData = [];
        foreach ($sort as $k => $v) {
            foreach ($list as $key => $val) {
                if ($val["id"] == $k) {
                    $retData[] = $val;
                }
            }
        }

        foreach ($list as $k => $v) {
            if (!in_array($v["id"], array_keys($sort))) {
                $retData[] = $v;
            }
        }
        return $retData;
    }
}
