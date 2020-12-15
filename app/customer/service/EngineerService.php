<?php


namespace app\customer\service;


use app\BaseService;
use app\mapper\EngineerMapper;
use app\mapper\UserAuthRowMapper;
use think\facade\Db;

class EngineerService extends BaseService
{
    protected $mapper = EngineerMapper::class;

    /**
     * 添加工程师
     * @param $param
     * @return bool
     */
    public function addEngineer($param) {
        Db::startTrans();
        try {
            # 添加工程师
            $res = $this->add($param);
            if (!$res)
                throw new \Exception("添加失败");
            # 添加该行可见权限
            $addData = [
                "type" => "engineer_id",
                "type_id" => $res->id,
                "user_id" => request()->uid,
                "status" => 1,
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserAuthRowMapper())->add($addData);
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
