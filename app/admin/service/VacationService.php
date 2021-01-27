<?php


namespace app\admin\service;


use app\mapper\VacationMapper;

class VacationService extends BaseService
{
    protected $mapper = VacationMapper::class;

    /**
     * 添加/修改休假信息
     * @param $param
     * @return bool
     */
    public function putVacation($param) {
        if (isset($param["add"]) && !empty($param["add"])) {
            foreach ($param["add"] as $k => $v) {
                $param["add"][$k]["create_time"] = time();
                $param["add"][$k]["status"] = 0;
                $param["add"][$k]["user_id"] = request()->uid;
                $param["add"][$k]["vacation_time"] = strtotime($v["vacation_time"]." 09:00:00");
                unset($param["add"][$k]["name"]);
                unset($param["add"][$k]["_XID"]);
            }
        }
        if (isset($param["update"]) && !empty($param["update"])) {
            foreach ($param["update"] as $k => $v) {
                $param["update"][$k]["vacation_time"] = strtotime($v["vacation_time"]." 09:00:00");
                unset($param["update"][$k]["create_time"]);
                unset($param["update"][$k]["user_id"]);
                unset($param["update"][$k]["_XID"]);
                unset($param["update"][$k]["show"]);
                unset($param["update"][$k]["status"]);
            }
        }
        return (new VacationMapper())->putVacation($param);
    }

    /**
     * 获取请假列表
     * @return mixed
     */
    public function vacations() {
        $data = (new VacationMapper())->vacations();
        foreach ($data as $k => $v) {
            $data[$k]["vacation_time"] = date("Y-m-d", $v["vacation_time"]);
            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["status"] = $v["status"]==0?"休班":"已取消休班";
            $data[$k]["show"] = $v["vacation_time"] > time() && $v["status"]==0;
        }
        return $data;
    }

    /**
     * 取消休假
     * @param $param
     * @return mixed
     */
    public function cancelVacation($param) {
        return $this->updateWhere(["id" => $param["id"]], ["status" => 1]);
    }
}