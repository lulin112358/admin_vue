<?php


namespace app\contracts\upload;


abstract class File implements IUpload
{
    abstract public function path($path);

    abstract public function name($name);
}
