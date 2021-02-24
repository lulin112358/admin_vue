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
            ->join(["user_extend" => "ue"], "ue.user_id=u.id")
            ->where($where)
            ->where(["u.work_nature" => 1])
            ->field("ue.actual_age, u.name, u.department, a.result, a.type, a.late_time, a.reward, a.note, a.create_time, a.id, 
            u.id as user_id, a.work_time, a.check_in_time, a.check_out_time, a.type, ue.entry_time, ue.create_time as tmp_entry_time")
            ->orderRaw("if(check_in_time=0, 1, 0)")
            ->order("check_in_time asc")
            ->select()->toArray();
    }

    public function checkOutInfo() {
        return Db::table("attendance")
            ->where(["user_id" => request()->uid])
            ->whereNotIn("type", "4,5")
            ->field("check_out_time, create_time, check_in_time")
            ->order("create_time desc")
            ->limit(0, 2)
            ->select()->toArray();
    }
}
