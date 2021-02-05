<?php


namespace app\admin\workerman;


use app\admin\service\TaskUserService;
use app\mapper\TaskUserMapper;
use think\facade\Db;
use think\worker\Server;
use Workerman\Lib\Timer;

class TaskWorker extends Server
{
    protected $host = "0.0.0.0";
    protected $port = 2346;
    protected $protocol = "websocket";
    protected $socket = "http://0.0.0.0:2346";

    private $cycleType = [
        1 => "everyDay",
        2 => "nDay",
        3 => "everyHour",
        4 => "nHour",
        5 => "nMinutes",
        6 => "nWeek",
        7 => "everyMonth"
    ];

    protected $users = [];

    public function onWorkerStart($worker) {
        # 心跳
        Timer::add(60, function()use($worker){
            $time_now = time();
            foreach($worker->connections as $connection) {
                if (!empty($connection->uid)) {
                    $this->users[$connection->uid] = $connection;
                }
                # 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                if (empty($connection->lastMessageTime)) {
                    $connection->lastMessageTime = $time_now;
                    continue;
                }
                # 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $connection->lastMessageTime > 65) {
                    $connection->close();
                }
            }

            # 查询所有任务和任务所有人
            $tasks = (new TaskUserMapper())->listenTasks();
            $userIdArr = array_keys($this->users);
            foreach ($tasks as $k => $v) {
                if ($v["type"] == 1) {
                    $flag = false;
                    if ($v["cycle_count"] > 0) {
                        $flag = date("Y-m-d H:i:00", time()) == date("Y-m-d H:i:00", $v["process_time"]);
                    }
                }else {
                    $flag = (new TaskHandle())->{$this->cycleType[$v["cycle_type"]]}($v);
                }
                if ($flag) {
                    if (in_array($v["user_id"], $userIdArr)) {
                        $this->users[$v["user_id"]]->send(json_encode(["lock" => true, "content" => $v["task_content"], "title" => $v["task_name"]]));
                    }
                    (new TaskUserMapper())->updateWhere(["id" => $v["id"]], ["cycle_count" => Db::raw("cycle_count + 1")]);
                }
            }
        });
    }

    public function onWorkerReload($worker) {

    }

    public function onConnect($connection) {

    }

    public function onMessage($connection, $data) {
        $data = json_decode($data, true);
        if (isset($data["uid"])) {
            $connection->uid = $data["uid"];
        }
        if (isset($data["first"]) && $data["first"]) {
            # 查询是否需要锁屏
            $uid = $data["uid"]??0;
            $isNeedLock = (new TaskUserService())->isNeedLock(["tu.user_id" => $uid]);
            $connection->send(json_encode($isNeedLock));
        }
        if (isset($data["unlock"]) && $data["unlock"] == true) {
            if (isset($this->users[$data["uid"]])) {
                $this->users[$data["uid"]]->send(json_encode(["lock" => false]));
            }
        }
        $connection->lastMessageTime = time();
    }

    public function onClose($connection) {
        unset($this->users[$connection->uid]);
    }

    public function onError($connection, $code, $msg) {
        unset($this->users[$connection->uid]);
        echo "error [ $code ] $msg\n";
    }
}
