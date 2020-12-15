<?php


namespace app\customer\service;


use app\BaseService;
use app\mapper\DegreeMapper;

class DegreeService extends BaseService
{
    protected $mapper = DegreeMapper::class;

    /**
     * 获取所有学历
     * @return mixed
     */
    public function degrees() {
        return (new DegreeMapper())->all();
    }
}
