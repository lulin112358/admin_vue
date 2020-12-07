<?php


namespace app\validate;


use think\Validate;

class RefundValidate extends Validate
{
    protected $rule = [
        "client_alipay|客户支付宝" => "requireWithout:client_wechat",
        "client_wechat|客户微信" => "requireWithout:client_alipay",
        "client_name|客户姓名" => "require",
        "refund_amount|退款金额" => "require",
        "refund_reason|退款原因" => "require",
        "order_id|订单id" => "require",
        "id|退款订单id" => "require"
    ];

    protected $scene = [
        "add" => ["client_alipay", "client_wechat", "client_name", "refund_amount", "refund_reason"],
        "refund" => ["id", "refund_amount"]
    ];
}
