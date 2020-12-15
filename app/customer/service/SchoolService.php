<?php


namespace app\customer\service;


use app\BaseService;
use app\mapper\AreaMapper;
use app\mapper\SchoolMapper;

class SchoolService extends BaseService
{
    protected $mapper = SchoolMapper::class;
    /**
     * 获取学校信息
     * @param $param
     * @return mixed
     */
    public function schools($param) {
        $level = $param["level"]??1;
        $pid = $param["pid"]??0;
        $levelMap = [
            1 => "province",
            2 => "city"
        ];
        if ($level < 3) {
            $data = (new AreaMapper())->selectBy(["level" => $levelMap[$level], "parent_id" => $pid], "id, parent_id as pid, name");
            if ($level == 2) {
                $pids = (new SchoolMapper())->columnBy([], "city");
                foreach ($data as $k => $v) {
                    if (in_array($v["id"], $pids)) {
                        $data[$k]["leaf"] = false;
                    }else {
                        $data[$k]["leaf"] = true;
                    }
                }
            }else {
                foreach ($data as $k => $v) {
                    $data[$k]["leaf"] = false;
                }
            }
        }else {
            $data = $this->selectBy(["city" => $pid], "id, name");
            foreach ($data as $k => $v) {
                $data[$k]["leaf"] = true;
            }
        }
        return $data;
    }
}
