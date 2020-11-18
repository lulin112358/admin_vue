<?php


namespace app\validate;


use think\Validate;

class OriginValidate extends Validate
{
    protected $rule = [
        "origin_id" => "require",
        "id" => "require",
        "origin_name|来源简称" => "require",
        "commission_ratio|提成比例" => "require",
        "market_user|市场专员" => "require",
        "market_manager|市场经理" => "require",
        "market_maintain|市场维护" => "require"
    ];

    protected $scene = [
        "info" => ["origin_id"],
        "del" => ["origin_id"],
        "add" => ["origin_name", "commission_ratio", "market_user", "market_manager", "market_maintain"],
        "update" => ["id", "origin_name", "commission_ratio", "market_user", "market_manager", "market_maintain"],
    ];
}
