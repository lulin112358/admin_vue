<?php


namespace app\admin\service;


use app\mapper\AccountCateMapper;
use app\mapper\AccountMapper;
use app\mapper\OrdersAccountMapper;
use app\mapper\OriginMapper;
use app\mapper\OriginOrdersAccountMapper;
use app\mapper\OriginUserMapper;
use think\facade\Db;

class OriginService extends BaseService
{
    protected $mapper = OriginMapper::class;

    /**
     * 添加市场来源
     *
     * @param $data
     * @return bool
     */
    public function addOrigin($data) {
        # 现有来源/新账号
        if ($data["cate"][0] == 1 && $data["cate"][1] == 2) {
            return $this->oldNew($data);
        }
        # 现有来源/现有账号
        if ($data["cate"][0] == 1 && $data["cate"][1] == 4) {
            return $this->oldOld($data);
        }
        # 新来源/新接单账号
        if ($data["cate"][0] == 0 && $data["cate"][1] == 1) {
            return $this->newNew($data);
        }
        # 新来源/现有账号
        if ($data["cate"][0] == 0 && $data["cate"][1] == 3) {
            return $this->newOld($data);
        }
    }


    /**
     * 新来源新账号
     *
     * @param $data
     * @return bool
     */
    private function newNew($data) {
        Db::startTrans();
        try {
            $origin = [
                "origin_name" => $data["origin"],
                "commission_ratio" => $data["commission"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($origin);
            if (!$res)
                throw new \Exception("添加失败");
            $origin_user = [
                "origin_id" => $res->id,
                "market_user" => $data["commissioner"],
                "market_manager" => $data["manager"],
                "market_maintain" => $data["maintain"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OriginUserMapper())->add($origin_user);
            if (!$res)
                throw new \Exception("添加失败!");
            if (!is_int($data["account_type"])) {
                $account_cate = [
                    "cate_name" => $data["account_type"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $res = (new AccountCateMapper())->add($account_cate);
                if (!$res)
                    throw new \Exception("添加失败啦");
                $data["account_cate"] = $res->id;
            }
            $account = [
                "account" => $data["account"],
                "account_cate" => $data["account_cate"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new AccountMapper())->add($account);
            if (!$res)
                throw new \Exception("添加失败啦!");
            $order_account = [
                "account_id" => $res->id,
                "nickname" => $data["nickname"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OrdersAccountMapper())->add($order_account);
            if (!$res)
                throw new \Exception("添加失败啦!!");
            $origin_order_account = [
                "origin_id" => $origin_user["origin_id"],
                "orders_account_id" => $res->id,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OriginOrdersAccountMapper())->add($origin_order_account);
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
     * 新来源现有账号
     *
     * @param $data
     * @return bool
     */
    private function newOld($data) {
        Db::startTrans();
        try {
            $origin = [
                "origin_name" => $data["origin"],
                "commission_ratio" => $data["commission"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($origin);
            if (!$res)
                throw new \Exception("添加失败");
            $origin_user = [
                "origin_id" => $res->id,
                "market_user" => $data["commissioner"],
                "market_manager" => $data["manager"],
                "market_maintain" => $data["maintain"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OriginUserMapper())->add($origin_user);
            if (!$res)
                throw new \Exception("添加失败啦");
            $res = (new OrdersAccountMapper())->findBy(["account_id" => $data["account"], "nickname" => $data["nickname"]]);
            $orders_account_id = $res["id"];
            if (!$res) {
                $order_account = [
                    "account_id" => $data["account"],
                    "nickname" => $data["nickname"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $res = (new OrdersAccountMapper())->add($order_account);
                if (!$res)
                    throw new \Exception("添加失败!");
                $orders_account_id = $res->id;
            }
            $origin_order_account = [
                "origin_id" => $origin_user["origin_id"],
                "orders_account_id" => $orders_account_id,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OriginOrdersAccountMapper())->add($origin_order_account);
            if (!$res)
                throw new \Exception("添加失败啦!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }


    /**
     * 现有来源新账号
     *
     * @param $data
     * @return bool
     */
    private function oldNew($data) {
        Db::startTrans();
        try {
            $account = [
                "account" => $data["account"],
                "account_cate" => $data["account_type"],
                "create_time" => time(),
                "update_time" => time()
            ];
            if (!is_int($data["account_type"])) {
                $account_cate = [
                    "cate_name" => $data["account_type"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $res = (new AccountCateMapper())->add($account_cate);
                if (!$res)
                    throw new \Exception("添加失败");
                $account["account_cate"] = $res->id;
            }
            $account_res = (new AccountMapper())->add($account);
            if (!$res)
                throw new \Exception("添加失败啦");
            $order_account = [
                "account_id" => $account_res->id,
                "nickname" => $data["nickname"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OrdersAccountMapper())->add($order_account);
            if (!$res)
                throw new \Exception("添加失败!!");
            $ooa = [
                "origin_id" => $data["origin"],
                "orders_account_id" => $res->id,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OriginOrdersAccountMapper())->add($ooa);
            if (!$res)
                throw new \Exception("添加失败啦!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }


    /**
     * 现有来源现有账号
     *
     * @param $data
     * @return bool
     */
    private function oldOld($data) {
        Db::startTrans();
        try {
            $res = (new OrdersAccountMapper())->findBy(["account_id" => $data["account"], "nickname" => $data["nickname"]]);
            $orders_account_id = $res["id"];
            if (!$res) {
                $order_account = [
                    "account_id" => $data["account"],
                    "nickname" => $data["nickname"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $res = (new OrdersAccountMapper())->add($order_account);
                if (!$res)
                    throw new \Exception("添加失败");
                $orders_account_id = $res->id;
            }
            $origin_order_account = [
                "origin_id" => $data["origin"],
                "orders_account_id" => $orders_account_id,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OriginOrdersAccountMapper())->add($origin_order_account);
            if (!$res)
                throw new \Exception("添加失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
