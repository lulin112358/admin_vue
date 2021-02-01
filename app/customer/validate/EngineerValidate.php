<?php


namespace app\customer\validate;


use think\Validate;

class EngineerValidate extends Validate
{
    protected $rule = [
        "contact_qq|QQ" => "require|unique:engineer",
        "qq_nickname|QQ昵称" => "require",
        "contact_phone|电话" => "require|unique:engineer",
        "top_degree_id|最高学历" => "require",
        "profession_id|专业" => "require",
        "school_id|学校" => "require",
        "good_at_software_id|擅长软件" => "require",
        "tendency_id|倾向类型" => "require",
        "alipay|支付宝" => "require",
        "collection_code|收款码" => "require"
    ];

    protected $scene = [
        "add" => ["contact_qq", "qq_nickname", "contact_phone", "top_degree_id", "profession_id", "school_id",
            "good_at_software_id", "tendency_id", "alipay", "collection_code"],
        "update" => ["collection_code"]
    ];
}
