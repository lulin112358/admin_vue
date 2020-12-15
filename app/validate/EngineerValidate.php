<?php


namespace app\validate;


use think\Validate;

class EngineerValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "status|状态" => "require",
        "alipay|支付宝" => "require",
        "contact_phone|电话" => "require",
        "contact_qq|QQ" => "require",
        "good_at_software_id|擅长软件" => "require",
        "name|姓名" => "require",
        "personnel_id|人事" => "require",
        "personnel_manager_id|人事主管" => "require",
        "profession_id|专业" => "require",
        "qq_nickname|QQ昵称" => "require",
        "school_id|学校" => "require",
        "tendency_id|倾向类型" => "require",
        "top_degree_id|最高学历" => "require",
        "wechat|微信号" => "require",
        "wechat_nickname|微信昵称" => "require",
        "query|查询条件" => "require"
    ];

    protected $scene = [
        "update" => ["id"],
        "add" => ["alipay", "contact_phone", "contact_qq", "good_at_software_id", "personnel_id",
            "personnel_manager_id", "profession_id", "qq_nickname", "school_id", "tendency_id", "top_degree_id"],
        "del" => ["id"],
        "query" => ["query"]
    ];
}
