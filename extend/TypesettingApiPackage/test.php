<?php
namespace TypesettingApiPackage;

require_once __DIR__."/../../vendor/autoload.php";

$tTApiManager = new TTApiManager();
//上传
$paperData = [
    'title'=>'测试接口',
    'author'=>'测试作者',
    'filePath'=>'./data/aaa.docx',
    'schemaId'=> 154,
    'rid'=> '13da1942dccdc2e6c9de49abf0d3d257'
];

//$res = $tTApiManager->schemaIdList();

//$res = $tTApiManager->uploadPaper($paperData,false);  //R20020900740111392
$res = $tTApiManager->reUploadPaper($paperData);
//$res = $tTApiManager->recordFind("98c61b0e8614ec2226ce59ccf5550592");

var_dump($res);