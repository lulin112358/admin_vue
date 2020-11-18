<?php


namespace app\validate;


use think\Validate;

class GroupChatValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "group_chat_name|群名称" => "require|unique:group_chat"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["group_chat_name"],
        "update" => ["id", "group_chat_name"],
        "del" => ["id"]
    ];
}
