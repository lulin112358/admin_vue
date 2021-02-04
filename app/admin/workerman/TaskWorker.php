<?php


namespace app\admin\workerman;


use think\worker\Server;

class TaskWorker extends Server
{
    protected $socket = 'http://0.0.0.0:2346';
}
