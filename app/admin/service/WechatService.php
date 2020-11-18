<?php


namespace app\admin\service;


use app\mapper\WechatMapper;
use think\facade\Db;

class WechatService extends BaseService
{
    protected $mapper = WechatMapper::class;


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
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($wechatData);
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
