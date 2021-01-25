<?php


namespace app\admin\controller;

use app\Code;
use think\facade\Filesystem;

class Upload extends Base
{
    /**
     * 文件上传
     */
    public function upload() {
        $filename = \upload\Upload::driver("file")->path("uploads")->save();
        $this->ajaxReturn(Code::SUCCESS, "success", $filename);
    }

    public function uploadOrderDoc() {
        $file = request()->file("file");
        $filename = $file->getOriginalName();
        $saveFilename = Filesystem::disk("public")->putFile("uploads", $file);
        $this->ajaxReturn(["filename" => $filename, "save_filename" => $saveFilename]);
    }

    public function editorUpload() {
        $filename = \upload\Upload::driver("file")->path("uploads")->save();
        exit(json_encode(["errno" => 0, "data" => ["http://api.erp2020.top/storage/".$filename]]));
//        exit(json_encode(["errno" => 0, "data" => ["http://tperp.io/storage/".$filename]]));
    }
}
