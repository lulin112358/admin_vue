<?php


namespace app\mapper;


use app\model\UserAuthFields;
use think\facade\Db;

class UserAuthFieldsMapper extends BaseMapper
{
    protected $model = UserAuthFields::class;

    /**
     * 分配权限
     *
     * @param $param
     * @param $data
     * @return bool
     */
    public function assignFields($param, $data) {
        Db::startTrans();
        try {
            $res = $this->deleteBy(["user_id" => $param["uid"]]);
            if ($res === false)
                throw new \Exception("操作失败");

            $res = $this->addAll($data);
            if (!$res)
                throw new \Exception("操作失败!");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
