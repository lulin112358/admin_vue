<?php


namespace app\admin\service;



use app\mapper\AccountCateMapper;
use app\mapper\AccountMapper;
use app\mapper\AmountAccountMapper;
use app\mapper\OrdersAccountMapper;
use app\mapper\OriginAmountAccountMapper;
use app\mapper\OriginMapper;
use app\mapper\OriginOrdersAccountMapper;
use app\mapper\OriginUserMapper;
use app\mapper\OriginWechatMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\UserMapper;
use app\mapper\WechatMapper;
use think\facade\Db;

class OriginService extends BaseService
{
    protected $mapper = OriginMapper::class;

    /**
     * 添加来源
     * @param $param
     * @return bool
     */
    public function addOrigin($param) {
        Db::startTrans();
        try {
            $res = $this->add($param);
            if (!$res)
                throw new \Exception("添加失败");
            # 添加来源可见权限
            $userAuthRowData = [
                "type" => "origin_id",
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
     * 来源列表
     *
     * @return mixed
     */
    public function originList() {
        $list = $this->selectBy(["status" => 1]);
        $users = (new UserMapper())->all("name, id");
        $users = array_combine(array_column($users, "id"), array_column($users, "name"));
        foreach ($list as $k => $v) {
            $list[$k]["market_maintain"] = $users[$v["market_maintain"]];
            $list[$k]["market_manager"] = $users[$v["market_manager"]];
            $list[$k]["market_user"] = $users[$v["market_user"]];
            $list[$k]["commission_ratio"] = $v["commission_ratio"] < 1 ? (($v["commission_ratio"] * 100)."%") : (floatval($v["commission_ratio"])."元");
        }
        return $list;
    }


    /**
     * 修改来源信息
     *
     * @param $data
     */
    public function updateOrigin($data) {
        Db::startTrans();
        try {
            $res = $this->updateWhere(["id" => $data["id"]], ["status" => 0]);
            if ($res === false)
                throw new \Exception("修改失败");
            unset($data["id"]);
            $data["create_time"] = time();
            $data["update_time"] = time();
            $res = $this->add($data);
            if (!$res)
                throw new \Exception("修改失败啦");
            # 添加来源可见权限
            $userAuthRowData = [
                "type" => "origin_id",
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

    /**
     * 市场来源信息
     *
     * @param $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
//    public function originInfo($data) {
//        # 视图origin_view
//        $data_ = Db::table("origin_view")->where(["id" => $data["origin_id"]])->find();
//        return $data_;
//    }

    /**
     * 添加市场来源
     *
     * @param $data
     * @return bool
     */
//    public function addOrigin($data) {
//        Db::startTrans();
//        try {
//            # 新增来源
//            if (is_string($data["origin"])) {
//                $originData = [
//                    "origin_name" => $data["origin"],
//                    "commission_ratio" => $data["commission"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $origin = (new OriginMapper())->add($originData);
//                if (!$origin)
//                    throw new \Exception("来源添加失败");
//                $data["origin"] = $origin->id;
//                # 新增来源人员关联关系
//                $originUserData = [
//                    "origin_id" => $origin->id,
//                    "market_user" => $data["commissioner"],
//                    "market_manager" => $data["manager"],
//                    "market_maintain" => $data["maintain"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $originUser = (new OriginUserMapper())->add($originUserData);
//                if (!$originUser)
//                    throw new \Exception("市场人员添加失败");
//            }
//            # 新增接单账号
//            if (is_string($data["account"])) {
//                # 新增接单账号类型
//                if (is_string($data["account_type"])) {
//                    $accountCateData = [
//                        "cate_name" => $data["account_type"],
//                        "create_time" => time(),
//                        "update_time" => time()
//                    ];
//                    $accountCate = (new AccountCateMapper())->add($accountCateData);
//                    if (!$accountCate)
//                        throw new \Exception("接单账号类型添加失败");
//                    $data["account_type"] = $accountCate->id;
//                }
//                # 新增接单账号
//                $accountData = [
//                    "account" => $data["account"],
//                    "account_cate" => $data["account_type"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $account = (new AccountMapper())->add($accountData);
//                if (!$account)
//                    throw new \Exception("添加接单账号失败");
//                $data["account"] = $account->id;
//                # 新增接单账号（昵称）
//                $ordersAccountData = [
//                    "account_id" => $data["account"],
//                    "nickname" => $data["nickname"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $ordersAccount = (new OrdersAccountMapper())->add($ordersAccountData);
//                if (!$ordersAccount)
//                    throw new \Exception("新增接单账号(昵称)失败");
//                # 新增接单账号(昵称)-市场来源关联关系
//                $originOrdersAccountData = [
//                    "origin_id" => $data["origin"],
//                    "orders_account_id" => $ordersAccount->id,
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $originOrdersAccount = (new OriginOrdersAccountMapper())->add($originOrdersAccountData);
//                if (!$originOrdersAccount)
//                    throw new \Exception("接单账号-市场来源关联失败");
//            }else {
//                # 判断接单账号和昵称是否存在，若不存在则新增
//                $ordersAccountMapper = new OrdersAccountMapper();
//                $exits = $ordersAccountMapper->findBy(["account_id" => $data["account"], "nickname" => $data["nickname"]]);
//                if (!$exits) {
//                    $ordersAccountData = [
//                        "account_id" => $data["account"],
//                        "nickname" => $data["nickname"],
//                        "create_time" => time(),
//                        "update_time" => time()
//                    ];
//                    $ordersAccount = $ordersAccountMapper->add($ordersAccountData);
//                    if (!$ordersAccount)
//                        throw new \Exception("添加接单昵称失败");
//                    # 添加接单账号(昵称)-市场来源关联
//                    $originOrdersAccountData = [
//                        "origin_id" => $data["origin"],
//                        "orders_account_id" => $ordersAccount->id,
//                        "create_time" => time(),
//                        "update_time" => time()
//                    ];
//                    $originOrdersAccount = (new OriginOrdersAccountMapper())->add($originOrdersAccountData);
//                    if (!$originOrdersAccount)
//                        throw new \Exception("接单账号-市场来源关联失败");
//                }
//                # 判断是否存在相关关联,若不存在则添加
//                $exits = (new OriginOrdersAccountMapper())->findBy(["origin_id" => $data["origin"], "orders_account_id" => $exits["id"]]);
//                if (!$exits) {
//                    $originOrdersAccountData = [
//                        "origin_id" => $data["origin"],
//                        "orders_account_id" => $exits["id"],
//                        "create_time" => time(),
//                        "update_time" => time()
//                    ];
//                    $originOrdersAccount = (new OriginOrdersAccountMapper())->add($originOrdersAccountData);
//                    if (!$originOrdersAccount)
//                        throw new \Exception("接单账号-市场来源关联失败");
//                }
//            }
//            # 新增收款账号
//            if (is_string($data["amount_account"])) {
//                # 新增收款账号
//                $amountAccountData = [
//                    "account" => $data["amount_account"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $amountAccount = (new AmountAccountMapper())->add($amountAccountData);
//                if (!$amountAccount)
//                    throw new \Exception("收款账号添加失败");
//                # 新增收款账号-市场来源关联
//                $originAmountAccountData = [
//                    "amount_account_id" => $amountAccount->id,
//                    "origin_id" => $data["origin"]
//                ];
//                $originAmountAccount = (new OriginAmountAccountMapper())->add($originAmountAccountData);
//                if (!$originAmountAccount)
//                    throw new \Exception("收款账号-市场来源关联添加失败");
//            }
//            if (is_string($data["wechat"])) {
//                # 新增沉淀微信
//                $wechatData = [
//                    "wechat" => $data["wechat"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $wechat = (new WechatMapper())->add($wechatData);
//                if (!$wechat)
//                    throw new \Exception("沉淀微信添加失败");
//                # 新增沉淀微信-市场来源关联
//                $originWechatData = [
//                    "wechat_id" => $wechat->id,
//                    "origin_id" => $data["origin"],
//                    "create_time" => time(),
//                    "update_time" => time()
//                ];
//                $originWechat = (new OriginWechatMapper())->add($originWechatData);
//                if (!$originWechat)
//                    throw new \Exception("沉淀微信-市场来源关联失败");
//            }
//            Db::commit();
//            return true;
//        }catch (\Exception $exception) {
//            Db::rollback();
//            return $exception->getMessage();
//        }
//    }

    /***
     * 来源列表
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
//    public function originList() {
//        # 视图origin_view
//        $list = Db::table("origin_view")->select()->toArray();
//        $users = (new UserMapper())->all("name, id");
//        $users = array_combine(array_column($users, "id"), array_column($users, "name"));
//        foreach ($list as $k => $v) {
//            $list[$k]["market_maintain"] = $users[$v["market_maintain"]];
//            $list[$k]["market_manager"] = $users[$v["market_manager"]];
//            $list[$k]["market_user"] = $users[$v["market_user"]];
//        }
//        return $list;
//    }
}
