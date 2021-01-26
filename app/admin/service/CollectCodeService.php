<?php


namespace app\admin\service;


use app\mapper\CollectCodeMapper;

class CollectCodeService extends BaseService
{
    protected $mapper = CollectCodeMapper::class;

    /**
     * 获取用户权限收款码
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userCollectCode() {
        $where = ["ccu.user_id" => request()->uid];
        return (new CollectCodeMapper())->userCollectCode($where);
    }
}
