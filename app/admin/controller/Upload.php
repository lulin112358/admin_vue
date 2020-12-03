<?php


namespace app\admin\controller;

use app\Code;

class Upload extends Base
{
    /**
     * 文件上传
     */
    public function upload() {
        $filename = \upload\Upload::driver("file")->path("uploads")->save();
        $this->ajaxReturn(Code::SUCCESS, "success", $filename);
    }
}
