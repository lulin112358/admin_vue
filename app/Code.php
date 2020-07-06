<?php


namespace app;


/**
 * 状态码统一管理
 * Class Code
 * @package app
 */
class Code
{
    const PARAM_VALIDATE = -1;          # 参数错误
    const SUCCESS = 1;                  # 请求成功
    const ERROR = 0;                    # 请求失败
    const JWT_ERROR = 4;                # jwt错误
}