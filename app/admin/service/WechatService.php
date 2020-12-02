<?php


namespace app\admin\service;


use app\mapper\UserAuthRowMapper;
use app\mapper\WechatMapper;
use think\facade\Db;

class WechatService extends BaseService
{
    protected $mapper = WechatMapper::class;

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
