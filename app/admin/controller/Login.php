<?php


namespace app\admin\controller;


use app\admin\service\UserService;
use app\Code;
use app\mapper\UserEngineersMapper;
use app\mapper\UserRoleMapper;
use app\model\OrdersMain;
use app\validate\UserValidate;
use jwt\Jwt;
use think\facade\Db;

class Login extends Base
{
    /**
     * 用户登录
     * @param UserService $service
     * @param UserValidate $validate
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(UserService $service, UserValidate $validate) {
        $param = input('param.');
        if (!$validate->scene('login')->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        $user = $service->findBy(["user_name" => $param["user_name"]]);
        if (!$user)
            $this->ajaxReturn(Code::ERROR, '该用户不存在');

        if (!password_verify($param['password'], $user['password']))
            $this->ajaxReturn(Code::ERROR, '密码错误');

        if ($user["status"] == 0) {
            $this->ajaxReturn(Code::ERROR, "该用户已锁定");
        }

        # 查找用户所属权限组
        $roles = (new UserRoleMapper())->columnBy(["user_id" => $user["id"]], "role_id");

        $data = [
            'uid' => $user['id']
        ];
        $jwt = Jwt::generateToken($data);
        $this->ajaxReturn(Code::SUCCESS, '登录成功', ["name" => $user["name"], "id" => $user["id"], "roles" => $roles], $jwt);
    }

//    public function test() {
//        $data = Db::table("engineer")->column("id");
//        $data1 = Db::table("user_engineers")->column("engineer_id");
//        $data2 = array_diff($data, $data1);
//        $data3 = Db::table("engineer")->where(["id" => $data2])->field("contact_qq as qq, contact_phone as phone, id as engineer_id")->select()->toArray();
//        foreach ($data3 as $k => $v) {
//            $data3[$k]["password"] = password_hash("123456", PASSWORD_DEFAULT);
//            $data3[$k]["create_time"] = time();
//            $data3[$k]["update_time"] = time();
//        }
//        Db::table("user_engineers")->insertAll($data3);
//    }

//    public function test() {
//        $data = Db::table("orders")->where(["is_split" => 0])->field("main_order_id, delivery_time")->select()->toArray();
//        foreach ($data as $k => $v) {
//            Db::table("orders")->where(["main_order_id" => $v["main_order_id"]])->update(["sort_delivery_time" => $v["delivery_time"]]);
//        }
//    }

//    public function test() {
//        $data = Db::table("orders")->field("main_order_id, status, id")->select()->toArray();
//        $tmp = [];
//        foreach ($data as $k => $v) {
//            $tmp[$v["main_order_id"]][] = $v;
//        }
//        foreach ($tmp as $k => $v) {
//            $arr = array_unique(array_column($v, "status"));
//            if (count($arr) == 1 && ($arr[0] == 3 || $arr[0] == 5)) {
//                Db::table("orders")->where(["main_order_id" => $v[0]["main_order_id"]])->update(["is_down" => 1]);
//            }
//        }
//    }

//    public function test() {
//        $map = [
//            1 => 2,
//            2 => 10,
//            3 => 10,
//            4 => 5,
//            5 => 6,
//            6 => 10,
//            7 => 8,
//            8 => 9,
//            9 => 10
//        ];
//        $data = Db::connect("old")->table("cd_gongchengshi")
//            ->where("gongchengshi_id", ">", 3711)
//            ->field("major2 as profession_id, name as qq_nickname, school_id, xieshou as contact_qq, xieshouphone as contact_phone, level as top_degree_id, software as good_at_software_id, fb_wenzhang_nums as core_journal_count, qx_order_type as tendency_id,weixinhao as wechat, weixin_nicheng as wechat_nickname, zhifubao as alipay, xingming as name, user_id as personnel_id, p_renshi_id as personnel_manager_id, qun_id as group_chat_id")
//            ->select()->toArray();
//        foreach ($data as $k => $v) {
//            $data[$k]["good_at_software_id"] = $map[$v["good_at_software_id"]];
//            $data[$k]["create_time"] = time();
//            $data[$k]["update_time"] = time();
//        }
//
//        $res = Db::table("engineer")->insertAll($data);
//        if (!$res)
//            echo "失败";
//        else
//            echo "成功";
//    }
}
