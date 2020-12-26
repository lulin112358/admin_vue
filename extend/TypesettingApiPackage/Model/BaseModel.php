<?php

namespace TypesettingApiPackage\Model;

class BaseModel
{
    /**
     * 配置key和secret
     * @var array
     */
    protected $config;

    public function __construct($test)
    {
        if($test){//线上测试账号
            $this->config = ['appKey'=>'210597643841919128','appSecret'=>'VQmOGraxqILh6A10MXeKEdU2z7WB'];
        }else{//线上正式账号
            $this->config = ['appKey'=>'210597643841919128', 'appSecret'=>'VQmOGraxqILh6A10MXeKEdU2z7WB'];
        }
    }

    /**
     * 请求加验签参数
     * @var array
     */
    protected $signParams = [];

    /**
     * 不加验签参数
     * @var array
     */
    protected $notSignParams = [];

    /**
     * 发送数据格式
     * @var string
     */
    protected $curlDataType="url";

    /**
     * 生成对应参数
     * @return array
     */
    public function getBuildArgs()
    {
        //验签
        $args = $this->getSignParams();
        $args['appKey'] = $this->config['appKey'];
        $args['time'] = time();
        // 对数组的值按key排序
        ksort($args);
        // 生成url的形式
        $params = http_build_query($args);
        // 生成sign
        $sign = md5($params.$this->config['appSecret']);
        $args['sign'] = $sign;
        $args['appKey'] = $this->config['appKey'];

        //加入非验签参数
        $args = array_merge($this->getNotSignParams(),$args);
        switch ($this->getCurlDataType()){
            case 'json':
                $args = json_encode($args);
        }
        return $args;
    }

    /**
     * @return array
     */
    public function getSignParams(): array
    {
        return $this->signParams;
    }

    /**
     * @return array
     */
    public function getNotSignParams(): array
    {
        return $this->notSignParams;
    }

    /**
     * @param string $curlDataType
     */
    public function setCurlDataType(string $curlDataType): void
    {
        $this->curlDataType = $curlDataType;
    }

    /**
     * @return string
     */
    public function getCurlDataType(): string
    {
        return $this->curlDataType;
    }

}
