<?php


namespace app\admin\service;


use app\mapper\AccountCateMapper;
use app\mapper\AccountMapper;
use app\mapper\OrdersAccountMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\WechatMapper;
use think\facade\Db;

class AccountService extends BaseService
{
    protected $mapper = AccountMapper::class;

    /**
     * 获取所有接单账号
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function account() {
        $list = Db::table("orders_account")->alias("oa")
            ->join(["account" => "a"], "a.id=oa.account_id", "left")
            ->join(["account_cate" => "ac"], "ac.id=a.account_cate", "left")
            ->where(["oa.status" => 1])
            ->field("oa.account_id, a.account, oa.id, ac.cate_name, oa.nickname, oa.is_wechat, a.simple_name")
            ->order("ac.cate_name desc")
            ->order("oa.id desc")
            ->select()->toArray();
        return $list;
    }

    /**
     * 获取账号信息
     *
     * @param $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountInfo($data) {
        return (new AccountMapper())->accountInfo($data);
    }

    /**
     * 添加接单账号
     *
     * @param $data
     * @return bool|string
     */
    public function addAccount($data) {
        Db::startTrans();
        try {
            if (is_string($data["account_cate"])) {
                # 新增接单账号类型
                $accountCateData = [
                    "cate_name" => $data["account_cate"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $res = (new AccountCateMapper())->add($accountCateData);
                if (!$res)
                    throw new \Exception("添加失败");
                $data["account_cate"] = $res->id;
            }
            # 判断该账号是否绑定其他接单账号类型/是否重复添加
            $cate = $this->findBy(["account" => $data["account"]]);
            if ($cate) {
                throw new \Exception("已存在该接单账号 请勿重复添加");
            }
            # 添加接单账号
            $accountData = [
                "account" => $data["account"],
                "simple_name" => $data["simple_name"],
                "account_cate" => $data["account_cate"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($accountData);
            if (!$res)
                throw new \Exception("添加失败!");
            $ordersAccountData = [
                "account_id" => $res->id,
                "nickname" => $data["nickname"],
                "is_wechat" => $data["is_wechat"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $re1 = (new OrdersAccountMapper())->add($ordersAccountData);
            if (!$re1)
                throw new \Exception("添加失败啦!");
            # 添加该账号可见权限
            $userAuthRowData = [
                "type" => "account_id",
                "type_id" => $re1->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($userAuthRowData);
            if (!$res)
                throw new \Exception("添加失败！！");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }


    /**
     * 修改接单账号
     *
     * @param $data
     * @return bool|string
     */
    public function updateAccount($data) {
        Db::startTrans();
        try {
            if (is_string($data["account_cate"])) {
                # 新增接单账号类型
                $accountCateData = [
                    "cate_name" => $data["account_cate"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $res = (new AccountCateMapper())->add($accountCateData);
                if (!$res)
                    throw new \Exception("修改失败");
                $data["account_cate"] = $res->id;
            }
            # 修改接单账号
            $res = $this->updateWhere(["account" => $data["account"]], ["account_cate" => $data["account_cate"], "simple_name" => $data["simple_name"]]);
            if ($res === false)
                throw new \Exception("修改失败!");
            $account_id = Db::table("orders_account")->where(["id" => $data["account_id"]])->value("account_id");
            $res = (new OrdersAccountMapper())->updateWhere(["account_id" => $account_id], ["status" => 0]);
            if ($res === false)
                throw new \Exception("修改失败啦");
            $ordersAccountData = [
                "account_id" => $account_id,
                "nickname" => $data["nickname"],
                "is_wechat" => $data["is_wechat"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new OrdersAccountMapper())->add($ordersAccountData);
            if (!$res)
                throw new \Exception("修改失败啦!");
            # 添加该账号可见权限
            $userAuthRowData = [
                "type" => "account_id",
                "type_id" => $res->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($userAuthRowData);
            if (!$res)
                throw new \Exception("添加失败！！");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }

    /**
     * 删除接单账号
     *
     * @param $data
     * @return bool|string
     */
    public function delAccount($data) {
        Db::startTrans();
        try {
            $account_id = (new OrdersAccountMapper())->findBy(["id" => $data["account_id"]])["account_id"];
            $res = (new OrdersAccountMapper())->updateWhere(["account_id" => $account_id], ["status" => 0]);
            if ($res === false)
                throw new \Exception("删除失败");
            $res = $this->updateWhere(["id" => $account_id], ["status" => 0]);
            if ($res === false)
                throw new \Exception("删除失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return $exception->getMessage();
        }
    }


    /**
     * 更新是否是沉淀微信
     * @param $param
     * @return mixed
     */
    public function updateIsWechat($param) {
        return (new OrdersAccountMapper())->updateWhere(["id" => $param["id"]], ["is_wechat" => $param["is_wechat"]]);
    }
}
