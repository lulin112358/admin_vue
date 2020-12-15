<?php


namespace app\customer\service;


use app\BaseService;
use app\mapper\ProfessionMapper;

class ProfessionService extends BaseService
{
    protected $mapper = ProfessionMapper::class;

    /**
     * 获取所有专业
     *
     * @param $param
     * @return mixed
     */
    public function professions($param) {
        $data = $this->selectBy($param);
        $pids = $this->columnBy([], "pid");
        foreach ($data as $k => $v) {
            if (in_array($v["id"], $pids)) {
                $data[$k]["leaf"] = false;
            }else {
                $data[$k]["leaf"] = true;
            }
        }
        return $data;
    }
}
