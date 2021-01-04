<?php


namespace app\admin\controller;

use app\Code;
use think\model\Collection;

/**
 * Class Base
 * @package app\admin\controller
 * @method ajaxReturn(...$cmdt)
 */
class Base
{

    /**
     * 需要权限列验证的接口    url => page
     * @var string[]
     */
    private $authColumnRequest = [
        "/admin/orders" => "客服订单管理页"
    ];

    /**
     * 需要权限行验证的接口
     * @var string[]
     */
    private $authRowRequest = [
        "/admin/orders",
        "/admin/wechat",
        "/admin/wechat_sort",
        "/admin/origin/list",
        "/admin/origin/sort_list",
        "/admin/account",
        "/admin/account_sort",
        "/admin/amount_account",
        "/admin/amount_account_sort",
//        "/admin/engineer",
        "/admin/user"
    ];

    /**
     * 需要过来行的映射
     * @var string[]
     */
    private $tableMap = [
        "/admin/wechat" => "wechat_id",
        "/admin/origin/list" => "origin_id",
        "/admin/account" => "account_id",
        "/admin/amount_account" => "amount_account_id",
        "/admin/engineer" => "engineer_id",
//        "/admin/user" => "user_id",
        "/admin/user/user_manager" => "user_user_manager_id",
        "/admin/user/customer_user" => "user_customer_id",
        "/admin/user/biller_user" => "user_biller_id",
        "/admin/user/almighty_user" => "user_almighty_id",
        "/admin/user/partner_user" => "user_partner_id",
        "/admin/user/part_time_editor" => "user_part_time_editor_id",
        "/admin/user/commissioner" => "user_commissioner_id",
        "/admin/user/maintain" => "user_maintain_id",
        "/admin/user/manager" => "user_manager_id",
    ];

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if ($name == 'ajaxReturn') {
            if (count($arguments) == 1) {
                if (is_string($arguments[0]))
                    exit(json_encode(['code' => Code::SUCCESS, 'msg' => $arguments[0], 'data' => null, 'token' => request()->token]));
                exit(json_encode(['code' => Code::SUCCESS, 'msg' => 'success', 'data' => $this->processData($arguments[0]), 'token' => request()->token]));
            }

            if (count($arguments) == 2)
                exit(json_encode(['code' => $arguments[0], 'msg' => $arguments[1], 'data' => null, 'token' => request()->token]));

            if (count($arguments) == 3)
                exit(json_encode(['code' => $arguments[0], 'msg' => $arguments[1], 'data' => $this->processData($arguments[2]), 'token' => request()->token]));

            exit(json_encode(['code' => $arguments[0], 'msg' => $arguments[1], 'data' => $this->processData($arguments[2]), 'token' => $arguments[3]]));
        }
    }

    /**
     * 处理数据可见列
     * @param $data
     * @return mixed|Collection
     */
    private function processData($data) {
        $url = explode("?", request()->url())[0];
        # 行权限控制
        if (in_array($url, $this->authRowRequest)) {
            if (request()->uid != 1) {
                if (is_array($data)) {
                    if (in_array($url, array_keys($this->tableMap)) && request()->method() == "GET") {
                        $data = array_values(collect($data)->whereIn("id", row_auth()[$this->tableMap[$url]]??[])->toArray());
                    }
                }else if ($data instanceof Collection) {
                    if (in_array($url, array_keys($this->tableMap)) && request()->method() == "GET") {
                        $data = $data->whereIn("id", row_auth()[$this->tableMap[$url]]??[]);
                    }
                }
            }
        }
        # 列权限控制
        if (in_array($url, array_keys($this->authColumnRequest)) && request()->method() == "GET") {
            $columns = column_auth($this->authColumnRequest[$url]);
            if ($url == "/admin/orders") {
                # 添加必须字段
                $columns[] = "main_order_id";
                $columns[] = "order_id";
                $columns[] = "color";
                $columns[] = "status_color";
            }
            if (request()->uid != 1) {
                if (is_array($data)) {
                    return visible($data, $columns);
                }else if ($data instanceof Collection) {
                    return $data->visible($columns);
                }else {
                    # 原样返回
                    return $data;
                }
            }
        }
        return $data;
    }
}
