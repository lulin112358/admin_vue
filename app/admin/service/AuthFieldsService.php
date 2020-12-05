<?php


namespace app\admin\service;


use app\mapper\AuthFieldsMapper;
use think\facade\Db;

class AuthFieldsService extends BaseService
{
    protected $mapper = AuthFieldsMapper::class;

    # 订单状态
    private $status = [
        ["value" => 1, "label" => "未发出"],
        ["value" => 2, "label" => "已发出"],
        ["value" => 3, "label" => "已交稿"],
        ["value" => 4, "label" => "准备退款"],
        ["value" => 5, "label" => "已退款"],
        ["value" => 6, "label" => "已发全能"]
    ];

    /**
     * 获取权限列管理列表
     *
     * @return array
     */
    public function authFields() {
        $list = $this->all();
        $data = [];
        foreach ($list as $k => $v) {
            $data[$v["page"]][] = $v;
        }
        $retData = [];
        foreach ($data as $k => $v) {
            $retData[] = [
                "field_name" => $k,
                "id" => 0,
                "create_time" => $v[0]["create_time"],
                "update_time" => $v[0]["update_time"],
                "children" => $v
            ];
        }
        return $retData;
    }


    /**
     * 获取orders用户可见列
     *
     * @return array
     */
    public function fields() {
        $fields = $this->all("field, field_name, is_edit, edit_type, data_source");
        $retFields = [
            [
                "type" => "seq",
                "title" => "序号",
                "width" => 60,
                "showHeaderOverflow" => true,
                "showOverflow" => true,
                "fixed" => "left"
            ],
            [
                "type" => "checkbox",
                "width" => 30,
                "minWidth" => 30,
                "fixed" => "right"
            ],
        ];
        foreach ($fields as $k => $v) {
            $field = [
                "field" => $v["field"],
                "title" => $v["field_name"],
                "minWidth" => 100,
                "showHeaderOverflow" => true,
                "showOverflow" => true
            ];
            if ($v["is_edit"]) {
                if ($v["edit_type"] == '$input') {
                    $field["editRender"] = [
                        "name" => '$input',
                        "attrs" => [
                            "type" => "text"
                        ]
                    ];
                }else if ($v["edit_type"] == '$select') {
                    if ($v["data_source"] != "") {
                        $data = Db::query($v['data_source']);
                    }
                    if ($v["field"] == "status") {
                        $data = $this->status;
                    }
                    $field["editRender"] = [
                        "name" => '$select',
                        "options" => $data,
                        "optionProps" => ["value" => "value", "label" => "label"]
                    ];
                }
            }
            $retFields[] = $field;
            $data = [];
        }
        $column = column_auth("客服订单管理页");
        foreach ($retFields as $k => $v) {
            if (isset($v["field"])) {
                if (!in_array($v["field"], $column)) {
                    unset($retFields[$k]);
                }
            }
        }
        return array_values($retFields);
    }
}
