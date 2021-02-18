<?php


namespace app\admin\workerman;


use app\mapper\TaskUserMapper;
use Carbon\Carbon;
use think\facade\Db;

class TaskHandle
{
    /**
     * 每天
     * @param $data
     * @return bool
     */
    public function everyDay($data) {
        if ($data["cycle_count"] > 1)
            return true;
        $config = json_decode($data["cycle_config"], true);
        if ((int)date("H", time()) == (int)$config["hours"] && (int)date("i", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            return true;
        }
        return false;
    }


    /**
     * n天
     * @param $data
     * @return bool
     */
    public function nDay($data) {
        if ($data["cycle_count"] > 1)
            return true;
        Carbon::setLocale("zh");
        $lastTime = (new TaskUserMapper())->findBy(["id" => $data["id"]], "last_time");
        $lastTime = Carbon::parse(date("Y-m-d 00:00:00", $lastTime["last_time"]));
        $carbon = new Carbon();
        $config = json_decode($data["cycle_config"], true);
        if ($carbon->diffInDays($lastTime) >= $config["day"]) {
            if ((int)date("H", time()) == (int)$config["hours"] && (int)date("i", time()) == (int)$config["minutes"]) {
                (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["last_time" => time(), "cycle_count" => Db::raw("cycle_count + 1")]);
                return true;
            }
        }
        return false;
    }


    /**
     * 每小时
     * @param $data
     * @return bool
     */
    public function everyHour($data) {
        if ($data["cycle_count"] > 1)
            return true;
        $config = json_decode($data["cycle_config"], true);
        if ((int)date("i", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            return true;
        }
        return false;
    }

    /**
     * n小时
     * @param $data
     * @return bool
     */
    public function nHour($data) {
        if ($data["cycle_count"] > 1)
            return true;
        Carbon::setLocale("zh");
        $lastTime = (new TaskUserMapper())->findBy(["id" => $data["id"]], "last_time");
        $lastTime = Carbon::parse(date("Y-m-d H:00:00", $lastTime["last_time"]));
        $carbon = new Carbon();
        $config = json_decode($data["cycle_config"], true);
        if ($carbon->diffInHours($lastTime) >= $config["hours"]) {
            if ((int)date("i", time()) == (int)$config["minutes"]) {
                (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["last_time" => time(), "cycle_count" => Db::raw("cycle_count + 1")]);
                return true;
            }
        }
        return false;
    }

    /**
     * n分钟
     * @param $data
     * @return bool
     */
    public function nMinutes($data) {
        if ($data["cycle_count"] > 1)
            return true;
        Carbon::setLocale("zh");
        $lastTime = (new TaskUserMapper())->findBy(["id" => $data["id"]], "last_time");
        $lastTime = Carbon::parse(date("Y-m-d H:i:00", $lastTime["last_time"]));
        $carbon = new Carbon();
        $config = json_decode($data["cycle_config"], true);
        if ($carbon->diffInMinutes($lastTime) >= $config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["last_time" => time(), "cycle_count" => Db::raw("cycle_count + 1")]);
            return true;
        }
        return false;
    }

    /**
     * 每周
     * @param $data
     * @return bool
     */
    public function nWeek($data) {
        if ($data["cycle_count"] > 1)
            return true;
        Carbon::setLocale("zh");
        $carbon = new Carbon();
        $weekDay = $carbon->weekday();
        if ($weekDay == 0) {
            $weekDay = 7;
        }
        $config = json_decode($data["cycle_config"], true);
        if ($weekDay == $config["week_day"] &&
            (int)date("H", time()) == (int)$config["hours"] &&
            (int)date("i", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            return true;
        }
        return false;
    }

    /**
     * 每月
     * @param $data
     * @return bool
     */
    public function everyMonth($data) {
        if ($data["cycle_count"] > 1)
            return true;
        $monthDay = (int)date("d", time());
        $config = json_decode($data["cycle_config"], true);
        if ($monthDay == (int)$config["month_day"] &&
            (int)date("H", time()) == (int)$config["hours"] &&
            (int)date("m", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            return true;
        }
        return false;
    }
}
