<?php


namespace upload;


use app\contracts\upload\File;
use think\facade\Filesystem;

class FileUpload extends File
{
    protected $path;
    protected $name;

    public function __construct($path = "uploads", $name = "file") {
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * 指定文件保存路径
     * @param $path
     * @return $this
     */
    public function path($path)
    {
        // TODO: Implement path() method.
        $this->path = $path;
        return $this;
    }

    /**
     * 指定文件接收name
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        // TODO: Implement name() method.
        $this->name = $name;
        return $this;
    }

    /**
     * 保存
     * @return bool|string
     */
    public function save()
    {
        // TODO: Implement save() method.
        $file = request()->file($this->name);
        return Filesystem::disk("public")->putFile($this->path, $file);
    }
}
