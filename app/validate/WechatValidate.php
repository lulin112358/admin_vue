<?php


namespace app\validate;


use think\Validate;

class WechatValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "wechat|沉淀微信" => "require|unique:wechat"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["wechat"],
        "update" => ["id", "wechat"],
        "del" => ["id"]
    ];
}
