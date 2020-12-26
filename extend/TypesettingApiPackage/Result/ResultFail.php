<?php

namespace  TypesettingApiPackage\Result;

class ResultFail
{
    public function __construct($errorMsg='未找到错误',$errorCode=4444){
        $this->setErrorCode($errorCode);
        $this->setErrorMag($errorMsg);
    }

    /**
     * 错误信息
     * @var string
     */
    private $errorMag;

    /**
     * 错误编码
     * @var int
     */
    private $errorCode;

    /**
     * @param int $errorCode
     */
    public function setErrorCode(int $errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorMag
     */
    public function setErrorMag(string $errorMag)
    {
        $this->errorMag = $errorMag;
    }

    /**
     * @return string
     */
    public function getErrorMag() : string
    {
        return $this->errorMag;
    }
}

















