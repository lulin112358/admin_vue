<?php


namespace app\automation\controller;


use app\Code;

/**
 * Class Base
 * @package app\automation\controller
 * @method ajaxReturn(...$cmdt)
 */
class Base
{
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if ($name == 'ajaxReturn') {
            if (count($arguments) == 1) {
                if (is_string($arguments[0]))
                    exit(json_encode(['code' => Code::SUCCESS, 'msg' => $arguments[0], 'data' => null, 'token' => request()->token]));
                exit(json_encode(['code' => Code::SUCCESS, 'msg' => 'success', 'data' => $arguments[0], 'token' => request()->token]));
            }

            if (count($arguments) == 2)
                exit(json_encode(['code' => $arguments[0], 'msg' => $arguments[1], 'data' => null, 'token' => request()->token]));

            if (count($arguments) == 3)
                exit(json_encode(['code' => $arguments[0], 'msg' => $arguments[1], 'data' => $arguments[2], 'token' => request()->token]));

            exit(json_encode(['code' => $arguments[0], 'msg' => $arguments[1], 'data' => $arguments[2], 'token' => $arguments[3]]));
        }
    }
}
