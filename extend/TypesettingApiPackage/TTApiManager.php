<?php
namespace TypesettingApiPackage;

use TypesettingApiPackage\Model\BaseModel;
use TypesettingApiPackage\Model\RecordModel;
use TypesettingApiPackage\Model\ReUploadModel;
use TypesettingApiPackage\Model\UploadModel;
use TypesettingApiPackage\Request\RecordRequest;
use TypesettingApiPackage\Request\ReUploadRequest;
use TypesettingApiPackage\Request\SchemaIdListRequest;
use TypesettingApiPackage\Request\UploadRequest;

class TTApiManager
{
    /**
     * 上传接口
     * @param array $paper 项目上传文件信息
     * @param bool $test
     * @return Result\DefaultResultSuccess|Result\ResultFail
     */
    public function uploadPaper($paper,$test = false)
    {
        $headerParams = [
            'Accept: application/json',
            'Content-Type: multipart/form-data'
        ];
        $uploadModel = new UploadModel($test);
        $uploadModel->setFile(new \CURLFile(realpath($paper['filePath'])));
        $uploadModel->setTitle($paper['title']);
        $uploadModel->setAuthor($paper['author']);
        $uploadModel->setSchemaId($paper['schemaId']);
        $uploadModel->setCurlDataType("array");
        /** @var UploadRequest $uploadRequest */
        $uploadRequest = new UploadRequest();
        $result = $uploadRequest->callContent($uploadModel,"post",$headerParams);
        return $result;
    }

    public function reUploadPaper($paper,$test = false)
    {
        $headerParams = [
            'Accept: application/json',
            'Content-Type: multipart/form-data'
        ];
        $reUploadModel = new ReUploadModel($test);
        $reUploadModel->setFile(new \CURLFile(realpath($paper['filePath'])));
        $reUploadModel->setTitle($paper['title']);
        $reUploadModel->setAuthor($paper['author']);
        $reUploadModel->setRid($paper['rid']);
        $reUploadModel->setSchemaId($paper['schemaId']);
        $reUploadModel->setCurlDataType("array");
        /** @var ReUploadRequest $reUploadRequest */
        $reUploadRequest = new ReUploadRequest();
        $result = $reUploadRequest->callContent($reUploadModel,"post",$headerParams);
        return $result;
    }

    /**
     * 获取报告
     * @param string $rid 接口记录id
     * @param bool $test
     * @return Result\DefaultResultSuccess|Result\ResultFail
     */
    public function recordFind($rid,$test = false)
    {
        $headerParams = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        /** @var RecordModel $recordModel */
        $recordModel= new RecordModel($test);
        $recordModel->setRid($rid);
        $recordModel->setCurlDataType('json');
        /** @var RecordRequest $recordRequest */
        $recordRequest = new RecordRequest();
        $result = $recordRequest->callContent($recordModel,'post',$headerParams);
        return $result;
    }

    /**
     * 获取模板id列表
     * @param bool $test
     * @return Result\DefaultResultSuccess|Result\ResultFail
     */
    public function schemaIdList()
    {
        $baseModel = new BaseModel(false);
        $baseModel->setCurlDataType('json');
        $headerParams = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        $request = new SchemaIdListRequest();
        $result = $request->callContent($baseModel,'post',$headerParams);
        return $result;
    }




}



