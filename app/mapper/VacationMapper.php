<?php


namespace app\mapper;


use app\model\Vacation;
use think\facade\Db;

class VacationMapper extends BaseMapper
{
    protected $model = Vacation::class;

    /**
     * 添加/修改休假信息
     * @param $param
     * @return bool
     */
    public function putVacation($param) {
        Db::startTrans();
        try {
            if (isset($param["add"]) && !empty($param["add"])) {
                $res = $this->addAll($param["add"]);
                if (!$res)
                    throw new \Exception("操作失败");
            }
            if (isset($param["update"]) && !empty($param["update"])) {
                $res1 = (new Vacation())->saveAll($param["update"]);
                if ($res1 === false)
                    throw new \Exception("操作失败!");
            }
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取请假列表
     * @return mixed
     */
    public function vacations() {
        return Db::table("vacation")->alias("v")
            ->join(["user" => "u"], "u.id=v.user_id")
            ->where(["v.user_id" => request()->uid])
            ->field("v.*, u.name")
            ->order("v.create_time desc")
            ->select()->toArray();
    }
}
