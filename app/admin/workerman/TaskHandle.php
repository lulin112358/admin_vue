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
        $flag = false;
        if ($data["cycle_count"] > 2) {
            $flag = true;
        }
        $config = json_decode($data["cycle_config"], true);
        if ((int)date("H", time()) == (int)$config["hours"] && (int)date("i", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            if ($data["cycle_count"] >= 2)
                $flag = true;
        }
        return $flag;
    }


    /**
     * n天
     * @param $data
     * @return bool
     */
    public function nDay($data) {
        $flag = false;
        if ($data["cycle_count"] > 2)
            $flag = true;
        Carbon::setLocale("zh");
        $lastTime = (new TaskUserMapper())->findBy(["id" => $data["id"]], "last_time");
        $lastTime = Carbon::parse(date("Y-m-d 00:00:00", $lastTime["last_time"]));
        $carbon = new Carbon();
        $config = json_decode($data["cycle_config"], true);
        if ($carbon->diffInDays($lastTime) >= $config["day"]) {
            if ((int)date("H", time()) == (int)$config["hours"] && (int)date("i", time()) == (int)$config["minutes"]) {
                (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["last_time" => time(), "cycle_count" => Db::raw("cycle_count + 1")]);
                if ($data["cycle_count"] >= 2)
                    $flag = true;
            }
        }
        return $flag;
    }


    /**
     * 每小时
     * @param $data
     * @return bool
     */
    public function everyHour($data) {
        $flag = false;
        if ($data["cycle_count"] > 2)
            $flag = true;
        $config = json_decode($data["cycle_config"], true);
        if ((int)date("i", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            if ($data["cycle_count"] >= 2)
                $flag = true;
        }
        return $flag;
    }

    /**
     * n小时
     * @param $data
     * @return bool
     */
    public function nHour($data) {
        $flag = false;
        if ($data["cycle_count"] > 2)
            $flag = true;
        Carbon::setLocale("zh");
        $lastTime = (new TaskUserMapper())->findBy(["id" => $data["id"]], "last_time");
        $lastTime = Carbon::parse(date("Y-m-d H:00:00", $lastTime["last_time"]));
        $carbon = new Carbon();
        $config = json_decode($data["cycle_config"], true);
        if ($carbon->diffInHours($lastTime) >= $config["hours"]) {
            if ((int)date("i", time()) == (int)$config["minutes"]) {
                (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["last_time" => time(), "cycle_count" => Db::raw("cycle_count + 1")]);
                if ($data["cycle_count"] >= 2)
                    $flag = true;
            }
        }
        return $flag;
    }

    /**
     * n分钟
     * @param $data
     * @return bool
     */
    public function nMinutes($data) {
        $flag = false;
        if ($data["cycle_count"] > 2)
            $flag = true;
        Carbon::setLocale("zh");
        $lastTime = (new TaskUserMapper())->findBy(["id" => $data["id"]], "last_time");
        $lastTime = Carbon::parse(date("Y-m-d H:i:00", $lastTime["last_time"]));
        $carbon = new Carbon();
        $config = json_decode($data["cycle_config"], true);
        if ($carbon->diffInMinutes($lastTime) >= $config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["last_time" => time(), "cycle_count" => Db::raw("cycle_count + 1")]);
            if ($data["cycle_count"] >= 2)
                $flag = true;
        }
        return $flag;
    }

    /**
     * 每周
     * @param $data
     * @return bool
     */
    public function nWeek($data) {
        $flag = false;
        if ($data["cycle_count"] > 2)
            $flag = true;
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
            if ($data["cycle_count"] >= 2)
                $flag = true;
        }
        return $flag;
    }

    /**
     * 每月
     * @param $data
     * @return bool
     */
    public function everyMonth($data) {
        $flag = false;
        if ($data["cycle_count"] > 2)
            $flag = true;
        $monthDay = (int)date("d", time());
        $config = json_decode($data["cycle_config"], true);
        if ($monthDay == (int)$config["month_day"] &&
            (int)date("H", time()) == (int)$config["hours"] &&
            (int)date("m", time()) == (int)$config["minutes"]) {
            (new TaskUserMapper())->updateWhere(["id" => $data["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
            if ($data["cycle_count"] >= 2)
                $flag = true;
        }
        return $flag;
    }

    /**
     * 每日提醒
     * @param $data
     * @return bool
     */
    public function everyDayRemind($data) {
        $flag = false;
        if ($data["cycle_type"] == 1) {
            if ((int)date("H") % 2 == 0 && (int)date("i") == 0) {
                $flag = true;
            }
        }
        return $flag;
    }
}
