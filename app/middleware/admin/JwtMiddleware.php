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
            (new Base())->ajaxReturn(Code::JWT_ERROR, '缺少token');
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
            (new Base())->ajaxReturn(Code::JWT_ERROR, 'token错误');
        }catch(\Firebase\JWT\BeforeValidException $e) {
            (new Base())->ajaxReturn(Code::JWT_ERROR, 'token还未生效');
        }catch(\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            (new Base())->ajaxReturn(Code::JWT_ERROR, 'token过期');
        }catch(Exception $e) {
            (new Base())->ajaxReturn(Code::JWT_ERROR, 'token解析出错');
        }

        return $next($request);
    }
}
