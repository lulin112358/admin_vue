<?php


namespace app\admin\service;


use app\mapper\CollectCodeUserMapper;
use app\mapper\UserMapper;
use think\facade\Db;

class CollectCodeUserService extends BaseService
{
    protected $mapper = CollectCodeUserMapper::class;

    /**
     * 获取权限列表
     * @param $param
     * @return array
     */
    public function collectCodeUser($param) {
        # 所有用户
        $users = (new UserMapper())->all("name as label, id as `key`");
        # 该收款码权限用户
        $authUsers = $this->columnBy(["collect_id" => $param["collect_id"]], "user_id");
        return ["users" => $users, "auth_users" => $authUsers];
    }

    /**
     * 分配权限
     * @param $param
     * @return bool
     */
    public function assignAuth($param) {
        Db::startTrans();
        try {
            $res = $this->deleteBy(["collect_id" => $param["collect_id"]]);
            if ($res === false)
                throw new \Exception("操作失败");
            $addData = [];
            foreach ($param["user_id"] as $k => $v) {
                $item = [
                    "user_id" => $v,
                    "collect_id" => $param["collect_id"],
                    "create_time" => time(),
                    "update_time" => time()
                ];
                $addData[] = $item;
            }
            $res1 = $this->addAll($addData);
            if (!$res1)
                throw new \Exception("操作失败拉");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
