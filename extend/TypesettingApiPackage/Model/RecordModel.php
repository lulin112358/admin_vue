<?php
namespace TypesettingApiPackage\Model;

class RecordModel extends BaseModel
{
    /**
     * 接口记录id
     * @var string
     */
    private $rid;

    /**
     * @param string $rid
     */
    public function setRid(string $rid): void
    {
        $this->rid = $rid;
        $this->signParams['rid'] = $rid;
    }

}