<?php


namespace app\admin\service;


use app\mapper\OrdersMainMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\WechatMapper;
use think\facade\Db;

class WechatService extends BaseService
{
    protected $mapper = WechatMapper::class;

    /**
     * 获取所有沉淀微信
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechats() {
        return (new WechatMapper())->wechats();
    }

    /**
     * 沉淀微信排序列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechatSort() {
        $list = (new WechatMapper())->wechats();
        $sort = array_count_values((new OrdersMainMapper())->columnBy(["customer_id" => request()->uid], "wechat_id"));
        arsort($sort);
        $retData = [];
        foreach ($sort as $k => $v) {
            foreach ($list as $key => $val) {
                if ($val["wechat_account_id"] == $k) {
                    $retData[] = $val;
                }
            }
        }

        foreach ($list as $k => $v) {
            if (!in_array($v["wechat_account_id"], array_keys($sort))) {
                $retData[] = $v;
            }
        }
        return $retData;
    }

//    /**
//     * 获取沉淀微信信息
//     * @param $param
//     * @return array|\think\Model|null
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\DbException
//     * @throws \think\db\exception\ModelNotFoundException
//     */
//    public function wechatInfo($param) {
//        return Db::table("orders_account")->alias("oa")
//            ->join(["account" => "a"], "oa.account_id=a.id")
//            ->where(["oa.status" => 1, "oa.is_wechat" => 1, "oa.id" => $param["id"]])
//            ->field("oa.id, oa.nickname as wechat, a.account as wechat_id, oa.create_time, oa.update_time")
//            ->find();
//    }

    /**
     * 添加沉淀微信
     * @param $param
     * @return bool
     */
    public function addWechat($param) {
        Db::startTrans();
        try {
            $res = $this->add($param);
            if (!$res)
                throw new \Exception("添加失败");
            # 添加可见权限
            $userAuthRowData = [
                "type" => "wechat_id",
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
     * 修改沉淀微信
     *
     * @param $data
     * @return bool|string
     */
    public function updateWechat($data) {
        Db::startTrans();
        try {
            $res = $this->updateWhere(["id" => $data["id"]], ["status" => 0]);
            if ($res === false)
                throw new \Exception("修改失败");
            $wechatData = [
                "wechat" => $data["wechat"],
                "wechat_id" => $data["wechat_id"],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($wechatData);
            if (!$res)
                throw new \Exception("修改失败啦");
            # 添加可见权限
            $userAuthRowData = [
                "type" => "wechat_id",
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
