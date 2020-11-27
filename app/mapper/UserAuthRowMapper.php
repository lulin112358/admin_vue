<?php


namespace app\mapper;


use app\model\UserAuthRow;
use think\facade\Db;

class UserAuthRowMapper extends BaseMapper
{
    protected $model = UserAuthRow::class;

    /**
     * 绑定权限
     *
     * @param $param
     * @param $data
     * @return bool
     */
    public function assignAuth($param, $data) {
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
