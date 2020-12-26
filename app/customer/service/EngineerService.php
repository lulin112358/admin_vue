<?php


namespace app\customer\service;


use app\BaseService;
use app\mapper\EngineerMapper;
use app\mapper\UserAuthRowMapper;
use app\mapper\UserEngineersMapper;
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
            # 添加工程师登录信息
            $accountData = [
                "engineer_id" => $res->id,
                "qq" => $param["contact_qq"],
                "phone" => $param["contact_phone"],
                "password" => password_hash("123456", PASSWORD_DEFAULT),
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = (new UserEngineersMapper())->add($accountData);
            if (!$res)
                throw new \Exception("添加失败啦!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }

    }
}
