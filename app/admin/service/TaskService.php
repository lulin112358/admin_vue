<?php


namespace app\admin\service;


use app\mapper\TaskMapper;

class TaskService extends BaseService
{
    protected $mapper = TaskMapper::class;

    private $type = [
        1 => "仅一次",
        2 => "重复"
    ];

    private $cycleType = [
        0 => "",
        1 => "每天",
        2 => "N天",
        3 => "每小时",
        4 => "N小时",
        5 => "N分钟",
        6 => "每星期",
        7 => "每月"
    ];
    private $week = [
        1 => "周一",
        2 => "周二",
        3 => "周三",
        4 => "周四",
        5 => "周五",
        6 => "周六",
        7 => "周日",
    ];

    private function processConfig($cycleType, $config) {
        switch ($cycleType) {
            case 1:
                $ret = "每天,".$config["hours"]."点".$config["minutes"]."分执行";
                break;
            case 2:
                $ret = "每".$config["day"]."天, 第".$config["hours"]."点".$config["minutes"]."分执行";
                break;
            case 3:
                $ret = "每小时, 第".$config["minutes"].'分钟执行';
                break;
            case 4:
                $ret = "每".$config["hours"].'小时,第'.$config["minutes"].'分钟执行';
                break;
            case 5:
                $ret = "每".$config["minutes"].'分钟执行';
                break;
            case 6:
                $ret = "每".$this->week[$config["week_day"]].', '.$config["hours"].'点'.$config["minutes"].'分执行';
                break;
            default:
                $ret = "每月, ".$config["month_day"].'日'.$config["hours"].'点'.$config["minutes"].'分执行';
                break;
        }
        return $ret;
    }

    /**
     * 任务
     * @return mixed
     */
    public function tasks() {
        $data = $this->all("*", "id desc");
        foreach ($data as $k => $v) {
            if ($v["type"] == 2) {
                $data[$k]["cycle_config"] = $this->processConfig($v["cycle_type"], json_decode($v["cycle_config"], true));
                $data[$k]["cycle_type"] = $this->cycleType[$v["cycle_type"]];
            }else{
                $data[$k]["cycle_config"] = date("Y-m-d H:i:s", $v["process_time"]);
                $data[$k]["cycle_type"] = "";
            }
            $data[$k]["type"] = $this->type[$v["type"]];
            $data[$k]["status"] = $v["status"]==1;
        }
        return $data;
    }

    /**
     * 添加任务
     * @param $param
     * @return mixed
     */
    public function addTask($param) {
        if (isset($param["cycle_config"]) && !empty($param["cycle_config"])) {
            $param["cycle_config"] = json_encode($param["cycle_config"]);
        }else {
            $param["cycle_config"] = "";
        }
        if ($param["type"] == 1) {
            $param["cycle_type"] = 0;
            $param["cycle_config"] = "";
            $param["process_time"] = strtotime($param["process_time"]);
        }else{
            $param["process_time"] = 0;
        }
        return $this->add($param);
    }

    /**
     * 修改任务
     * @param $param
     * @return mixed
     */
    public function updateTask($param) {
        if (isset($param["cycle_config"]) && !empty($param["cycle_config"])) {
            $param["cycle_config"] = json_encode($param["cycle_config"]);
        }else {
            $param["cycle_config"] = "";
        }
        if ($param["type"] == 1) {
            $param["cycle_type"] = 0;
            $param["cycle_config"] = "";
            $param["process_time"] = strtotime($param["process_time"]);
        }else{
            $param["process_time"] = 0;
        }
        return $this->updateBy($param);
    }
}
