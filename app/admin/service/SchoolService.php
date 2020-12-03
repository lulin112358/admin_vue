<?php


namespace app\admin\service;


use app\mapper\AreaMapper;
use app\mapper\SchoolMapper;
use think\facade\Db;

class SchoolService extends BaseService
{
    protected $mapper = SchoolMapper::class;

    /**
     * 学校列表
     *
     * @return array
     */
//    public function schools() {
//        $schoolData = $this->all("id, name, city");
//        $schoolData_ = [];
//        foreach ($schoolData as $k => $v) {
//            $schoolData_[$v["city"]][] = $v;
//        }
//        $areaData = (new AreaMapper())->selectBy(["level" => ["province", "city"]], "id, parent_id as pid, name");
//        $areaData = generateTree($areaData);
//        foreach ($areaData as $k => $v) {
//            if (isset($v["children"])) {
//                foreach ($v["children"] as $key => $val) {
//                    foreach ($schoolData_ as $index => $item) {
//                        if ($index == $val["id"]) {
//                            $areaData[$k]["children"][$key]["children"] = $item;
//                        }
//                    }
//                }
//            }
//        }
//        return $areaData;
//    }


    /**
     * 学校列表
     *
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
