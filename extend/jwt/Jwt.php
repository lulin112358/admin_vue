<?php


namespace jwt;


class Jwt
{
    /**
     * 生成jwt
     * @param $data
     * @return string
     */
    public static function generateToken($data) {
        $payload = [
            "iss" => config('jwt.iss'),
            "aud" => config('jwt.aud'),
            "iat" => time(),
            "nbf" => time(),
            "exp" => time()+3600*24*7,
            "data" => $data
        ];
        $jwt = \Firebase\JWT\JWT::encode($payload, config('jwt.key'));
        return $jwt;
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
}