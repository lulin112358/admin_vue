<?php


namespace upload;


class Upload
{
    static protected $driver = [
        "file" => FileUpload::class
    ];

    /**
     * 指定文件上传驱动 并返回该实例
     * @param $driver
     * @return mixed
     */
    static public function driver($driver) {
        $instance = self::$driver[$driver];
        return new $instance;
    }
}
