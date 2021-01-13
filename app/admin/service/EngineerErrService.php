<?php


namespace app\admin\service;


use app\mapper\EngineerErrMapper;
use app\mapper\ErrReadMapper;
use app\mapper\UserRoleMapper;
use think\facade\Db;

class EngineerErrService extends BaseService
{
    protected $mapper = EngineerErrMapper::class;

    /**
     * 报错订单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function errOrders($status) {
        $role = (new UserRoleMapper())->columnBy(["user_id" => request()->uid], "role_id");
        $data = (new EngineerErrMapper())->errOrders($status);
        $read = (new ErrReadMapper())->columnBy(["user_id" => request()->uid], "err_id");
        $data = collect($data)->whereNotIn("id", $read)->toArray();
        if (!in_array(1, $role)) {
            $data = collect($data)->where('biller', '=', request()->uid)
                ->where('customer_manager', '=', request()->uid)->toArray();
        }
        foreach ($data as $k => $v) {
            $data[$k]['create_time'] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]['update_time'] = date("Y-m-d H:i:s", $v["update_time"]);
        }
        $retData = ["show" => false];
        $biller = array_column($data, "biller");
        if (in_array(request()->uid, $biller)) {
            $retData["show"] = true;
        }
        $data = array_values($data);
        $retData["data"] = $data;
        return $retData;
    }

    /**
     * 处理报错
     * @param $param
     * @return mixed
     */
    public function dealErr($param) {
        # 获取订单发单人
        $biller = (new EngineerErrMapper())->orderBiller($param);
        $data = [
            "err_id" => $param["id"],
            "user_id" => request()->uid,
            "create_time" => time(),
            "update_time" => time()
        ];
        Db::startTrans();
        try {
            if (request()->uid == $biller) {
                $res = $this->updateWhere(["id" => $param["id"]], ["status" => 1, 'update_time' => time(), "deal_user" => request()->uid]);
                if ($res === false)
                    throw new \Exception("操作失败");
            }
            $res = (new ErrReadMapper())->add($data);
            if (!$res)
                throw new \Exception("操作失败啦");

            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取已读信息
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function alreadyRead() {
        $data = (new ErrReadMapper())->alreadyRead();
        foreach ($data as $k => $v) {
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
        }
        return $data;
    }

    /**
     * 获取站内信
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function stationLetters() {
        return  [
            "wait_deal" => $this->errOrders(0),
            "already_read" => $this->alreadyRead(),
            "already_deal" => $this->errOrders(1)
        ];
    }
}
