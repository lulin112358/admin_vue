<?php


namespace app\admin\service;


use app\mapper\TypesettingMapper;
use TypesettingApiPackage\TTApiManager;

class TypesettingService extends BaseService
{
    protected $mapper = TypesettingMapper::class;

    /**
     * 上传文档
     * @param $param
     * @return bool
     */
    public function uploadPaper($param) {
        try {
            $param["filePath"] = root_path()."public/storage/".$param["file_path"];
            $param["schemaId"] = explode("/", $param["schema_id"])[0];
            $res = (new TTApiManager())->uploadPaper($param);
            if (method_exists($res, "getErrorCode"))
                throw new \Exception($res->getErrorMag());

            $addData = [
                "user_id" => request()->uid,
                "title" => $param["title"],
                "author" => $param["author"],
                "file_path" => $param["file_path"],
                "rid" => $res->getData()["rid"],
                "school" => explode("/", $param["schema_id"])[1],
                "create_time" => time(),
                "update_time" => time()
            ];
            $res = $this->add($addData);
            if (!$res)
                throw new \Exception("失败");
            return true;
        }catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * 更新
     * @param $rid
     */
    public function recordFind() {
        $rid = $this->findBy(["status" => 0])["rid"];
        $data = (new TTApiManager())->recordFind($rid);
        if (method_exists($data, "getData")) {
            $data = $data->getData();
            if ($data["status"] == 3) {
                $this->updateWhere(["rid" => $rid], ["status" => 1, "pdf_path" => $data["pdf_download_url"], "docx_path" => $data["download_url"], "update_time" => time()]);
            }
        }
    }
}
