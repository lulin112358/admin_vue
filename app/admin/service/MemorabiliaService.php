<?php


namespace app\admin\service;


use app\mapper\MemorabiliaMapper;

class MemorabiliaService extends BaseService
{
    protected $mapper = MemorabiliaMapper::class;

    /**
     * 添加大事记
     * @param $param
     * @return mixed
     */
    public function addMemorabilia($param) {
        $data = [
            "user_id" => request()->uid,
            "content" => $param["content"],
            "create_time" => time()
        ];
        return $this->add($data);
    }
}
