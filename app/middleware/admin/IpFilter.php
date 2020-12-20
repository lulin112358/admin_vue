<?php
declare (strict_types = 1);

namespace app\middleware\admin;

use app\Code;
use app\model\IpWhite;

class IpFilter
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
        # 获取真实IP
        $ip = get_real_ip();
        # 获取合法IP
        $whiteIp = IpWhite::column("ip");
        if (!in_array($ip, $whiteIp)) {
            exit(json_encode(["code" => Code::ERROR, "msg" => "非法IP", "data" => null, "token" => ""]));
        }
        return $next($request);
    }
}
