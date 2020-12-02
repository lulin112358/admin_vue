<?php
declare (strict_types = 1);

namespace app\middleware\admin;

use app\admin\controller\Base;
use app\Code;
use jwt\Jwt;
use think\Exception;

class JwtMiddleware
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
        //
        $jwt = $request->header("token");
        if (!$jwt)
            exit(json_encode(["code" => Code::JWT_ERROR, "msg" => "缺少token", "data" => null, "token" => ""]));
        try {
            $decoded = Jwt::decodeToken($jwt);
            $data = (array)$decoded["data"];
            $request->user = $data;
            $request->uid = $data["uid"];
            if ($data["time"] <= time() + 3600) {
                $data["time"] = time() + 24 * 3600;
                $request->token = Jwt::generateToken($data);
            }else{
                $request->token = '';
            }
        }catch(\Firebase\JWT\SignatureInvalidException $e) {
            exit(json_encode(["code" => Code::JWT_ERROR, "msg" => "token错误", "data" => null, "token" => ""]));
        }catch(\Firebase\JWT\BeforeValidException $e) {
            exit(json_encode(["code" => Code::JWT_ERROR, "msg" => "token还未生效", "data" => null, "token" => ""]));
        }catch(\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            exit(json_encode(["code" => Code::JWT_ERROR, "msg" => "token过期", "data" => null, "token" => ""]));
        }catch(Exception $e) {
            exit(json_encode(["code" => Code::JWT_ERROR, "msg" => "token解析出错", "data" => null, "token" => ""]));
        }

        return $next($request);
    }
}
