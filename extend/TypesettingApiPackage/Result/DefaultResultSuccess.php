<?php
namespace TypesettingApiPackage\Result;

class DefaultResultSuccess
{
    /**
     * 回复信息
     * @var mixed
     */
    private $data;

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}









