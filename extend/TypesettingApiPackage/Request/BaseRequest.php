<?php
namespace TypesettingApiPackage\Request;

use AppBundle\AppClassFactory;
use TypesettingApiPackage\Model\BaseModel;
use TypesettingApiPackage\Result\DefaultResultSuccess;
use TypesettingApiPackage\Result\ResultFail;

class BaseRequest {

    public function __construct()
    {
        $this->apiUrl .= $this->tailUrl;
    }

    /**
     * 接口地址
     * @var string
     */
    protected $apiUrl='http://api.zaopaiban.com/v1'; //线上地址
//    protected $apiUrl='http://typesetting.report/app_dev.php/v1'; //线下地址

    /**
     * 尾部地址
     * @var string
     */
    protected $tailUrl='';

    protected $curlTimeOut = 30;

    /**
     * 请求入口
     * @param BaseModel $baseModel
     * @param $methods
     * @param $withCookie
     * @param null $headers
     * @return ResultFail|DefaultResultSuccess
     */
    public function callContent(BaseModel $baseModel,$methods="post",$headers=[],$withCookie=false)
    {
        $ret = $this->requestHttp($baseModel->getBuildArgs(),$methods,$withCookie,$headers,$baseModel->getCurlDataType());
        return $this->resultData($ret);
    }

    /**
     * 提取结果
     * @param $ret
     * @return ResultFail|DefaultResultSuccess
     */
    public function resultData($ret){
        try{
            if($ret instanceof ResultFail)return $ret;
            $data = json_decode($ret,true);
            if(isset($data['code']) and $data['code'] == 200){
                $resultSuccess = new DefaultResultSuccess();
                $resultSuccess->setData($data['data']);
                return $resultSuccess;
            }else{
                return new ResultFail((string)$data['codeMsg'],(int)$data['code']);
            }
        }catch (\Exception $e){
            return new ResultFail($e->getMessage(),5000);
        }
    }

    /**
     * 数据发送
     * @param $args
     * @param string $methods
     * @param bool $withCookie
     * @param array $headers
     * @param string $curlDataType
     * @return bool|ResultFail|string
     */
    protected function requestHttp($args,$methods = 'post',$withCookie = false,$headers=array(),$curlDataType="url"){
        $ch = curl_init();
        $url = $this->apiUrl;
        $curlDataType==='url'?$data = $this->convert($args):$data = $args;
        switch ($methods){
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'get':
                if($data)
                {
                    if(stripos($this->apiUrl, "?") > 0)
                    {
                        $url .= "&$data";
                    }
                    else
                    {
                        $url .= "?$data";
                    }
                }
                break;
            default:
                return new ResultFail("不支持的请求方式！",4444);
        }
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_HEADER, false);//设置不返回header
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_TIMEOUT, $this->curlTimeOut);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLINFO_HEADER_OUT, true);

        if(!empty($headers))curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($withCookie) curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);

        $r = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if($error) return new ResultFail("请求发生错误：".$error);

        return $r;
    }

    /**
     * 数据处理
     * @param $args
     * @return string
     */
    public function convert(&$args)
    {
        $data = '';
        if (is_array($args))
        {
            foreach ($args as $key=>$val)
            {
                if (is_array($val))
                {
                    foreach ($val as $k=>$v)
                    {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                }
                else
                {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }

}