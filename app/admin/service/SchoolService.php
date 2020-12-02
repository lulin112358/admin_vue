<?php


namespace app\admin\service;


use app\mapper\AreaMapper;
use app\mapper\SchoolMapper;

class SchoolService extends BaseService
{
    protected $mapper = SchoolMapper::class;

    /**
     * 学校列表
     *
     * @return array
     */
    public function schools() {
        $schoolData = $this->all("id, name, city");
        $schoolData_ = [];
        foreach ($schoolData as $k => $v) {
            $schoolData_[$v["city"]][] = $v;
        }
        $areaData = (new AreaMapper())->selectBy(["level" => ["province", "city"]], "id, parent_id as pid, name");
        $areaData = generateTree($areaData);
        foreach ($areaData as $k => $v) {
            if (isset($v["children"])) {
                foreach ($v["children"] as $key => $val) {
                    foreach ($schoolData_ as $index => $item) {
                        if ($index == $val["id"]) {
                            $areaData[$k]["children"][$key]["children"] = $item;
                        }
                    }
                }
            }
        }
        return $areaData;
    }
}
