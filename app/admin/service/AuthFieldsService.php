<?php


namespace app\admin\service;


use app\mapper\AuthFieldsMapper;
use app\mapper\CategoryMapper;
use app\mapper\RoleAuthFieldsEditMapper;
use app\mapper\UserAuthFieldsEditMapper;
use app\mapper\UserRoleMapper;
use think\facade\Db;

class AuthFieldsService extends BaseService
{
    protected $mapper = AuthFieldsMapper::class;

    # 订单状态
    private $status = [
        ["value" => 1, "label" => "未发出"],
//        ["value" => 2, "label" => "已发出"],
        ["value" => 3, "label" => "已交稿"],
        ["value" => 4, "label" => "准备退款"],
//        ["value" => 5, "label" => "已退款"],
        ["value" => 6, "label" => "已发全能"],
        ["value" => 7, "label" => "已发发单"],
        ["value" => 8, "label" => "返修中"],
    ];

    /**
     * 获取权限列管理列表
     *
     * @return array
     */
    public function authFields() {
        $list = $this->all();
        $data = [];
        $editData = [];
        foreach ($list as $k => $v) {
            $data[$v["page"]][] = $v;
            if ($v["is_edit"] == 1) {
                $v["id"] = "edit_".$v["id"];
                $editData[$v["page"]][] = $v;
            }
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
        $retEditData = [];
        foreach ($editData as $k => $v) {
            $retEditData[] = [
                "field_name" => "(可编辑列)".$k,
                "id" => -1,
                "create_time" => $v[0]["create_time"],
                "update_time" => $v[0]["update_time"],
                "children" => $v
            ];
        }
        return array_merge($retData, $retEditData);
    }


    /**
     * 获取orders用户可见列
     *
     * @return array
     */
    public function fields() {
        $roles = (new UserRoleMapper())->columnBy(["user_id" => request()->uid], "role_id");
        # 获取角色可编辑列权限
        $editFields = (new RoleAuthFieldsEditMapper())->columnBy(["role_id" => $roles], "field_id");
        # 获取用户可编辑列权限
        $userEditFields = (new UserAuthFieldsEditMapper())->selectBy(["user_id" => request()->uid], "field_id, type");
        # 根据该用户独有编辑权限进行增删
        foreach ($userEditFields as $k => $v) {
            if ($v["type"] == 1) {
                $editFields[] = $v["field_id"];
            }else {
                $key = array_search($v["field_id"], $editFields);
                unset($editFields[$key]);
            }
        }
        $editFields = array_values($editFields);
        $flag = in_array(1, $roles);
        $fields = $this->all("id, field, field_name, is_edit, edit_type, data_source");
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
                "showOverflow" => true,
            ];
            if ($v["field"] == "payment_log") {
                $field["cellRender"] = [
                    "name" => "paymentRender",
                    "events" => [
                        "click" => "paymentLogs"
                    ]
                ];
            }
            if ($flag && $v["field"] == "cate_name") {
                $data = (new CategoryMapper())->all("id as value, pid, cate_name as label");
                $data = generateTree($data, "children", "value");
                $field["editRender"] = [
                    "name" => 'cascader',
                    "options" => $data
                ];
            }
            if ($v["is_edit"]) {
                if (in_array($v["id"], $editFields)) {
                    if ($v["edit_type"] == '$input') {
                        $field["editRender"] = [
                            "name" => '$input',
                            "autoselect" => true,
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
