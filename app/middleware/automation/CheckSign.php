<?php
declare (strict_types = 1);

namespace app\middleware\automation;

use app\Code;
use rsa\Rsa;

class CheckSign
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 获取参数及签名
        $param = input("param.");
        if (!isset($param["sign"])) {
            exit(json_encode(["code" => Code::ERROR, "msg" => "缺少签名", "data" => null]));
        }
        $sign = str_replace(" ", "+", $param["sign"]);
        unset($param["sign"]);
        if (!Rsa::verify($param, $sign)) {
            exit(json_encode(["code" => Code::ERROR, "msg" => "签名错误", "data" => null]));
        }
        return $next($request);
    }
}
