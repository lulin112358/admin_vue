<?php


namespace app\mapper;


use app\model\Attendance;
use think\facade\Db;

class AttendanceMapper extends BaseMapper
{
    protected $model = Attendance::class;

    /**
     * 获取考勤数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function attendances($where) {
        return Db::table("attendance")->alias("a")
            ->join(["user" => "u"], "a.user_id=u.id", "right")
            ->where($where)
            ->where(["u.work_nature" => 1])
            ->field("u.name, u.department, a.result, a.type, a.late_time, a.reward, a.note, a.create_time, u.id as user_id")
            ->select()->toArray();
    }
}
