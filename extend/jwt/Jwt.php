<?php


namespace jwt;


class Jwt
{
    /**
     * 生成jwt
     * @param $data
     * @return array
     */
    public static function generateToken($data) {
        $payload = [
            "iss" => config('jwt.iss'),
            "aud" => config('jwt.aud'),
            "iat" => time(),
            "nbf" => time(),
            "exp" => time()+3600*24,
            "data" => $data
        ];
        $payload_refresh = [
            "iss" => config('jwt.iss'),
            "aud" => config('jwt.aud'),
            "iat" => time(),
            "nbf" => time(),
            "exp" => time()+3600*24*7,
            "data" => $data
        ];
        $jwt = \Firebase\JWT\JWT::encode($payload, config('jwt.key'));
        $jwt_refresh = \Firebase\JWT\JWT::encode($payload_refresh, config('jwt.key'));
        return [
            "jwt" => $jwt,
            "jwt_refresh" => $jwt_refresh
        ];
    }

    /**
     * 解析jwt
     * @param $jwt
     * @return array
     */
    public static function decodeToken($jwt) {
        $decoded = \Firebase\JWT\JWT::decode($jwt, config('jwt.key'), array('HS256'));
        return (array)$decoded;
    }

    /**
     * 刷新token
     * @param $jwt_refresh
     * @return array
     */
    public static function refreshToken($jwt_refresh) {
        $data = self::decodeToken($jwt_refresh);
        $data = (array)$data["data"];
        return self::generateToken($data);
    }
}