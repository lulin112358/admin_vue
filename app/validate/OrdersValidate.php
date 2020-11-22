<?php


namespace app\validate;


use think\Validate;

class OrdersValidate extends Validate
{
    protected $rule = [
        "origin_id|来源" => "require",
        "account_id|接单账号" => "require",
        "amount_account_id|收款账户" => "require",
        "total_amount|总价" => "require",
        "deposit_amount|定金" => "require",
        "customer_contact|客户联系方式" => "require",
        "customer_manager|客服主管" => "require",
        "cate_id|业务分支" => "require",
        "wechat_id|沉淀微信" => "require",
        "require|要求" => "require",
        "note|备注" => "require",
        "delivery_time|交稿时间" => "require",
        "field|变动字段" => "require",
        "value|变动值" => "require",
        "main_order_id" => "require",
        "order_id" => "require"
    ];

    protected $scene = [
        "add" => ["origin_id", "account_id", "amount_account_id", "total_amount", "customer_contact", "customer_manager",
            "cate_id", "wechat_id", "require", "note", "delivery_time", "deposit_amount"],
        "update" => ["field", "value", "main_order_id", "order_id"],
        "del" => ["order_id"],
        "split" => ["main_order_id"]
    ];
}
