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

        # 查找用户所属权限组
        $roles = (new UserRoleMapper())->columnBy(["user_id" => $user["id"]], "role_id");

        $data = [
            'uid' => $user['id']
        ];
        $jwt = Jwt::generateToken($data);
        $this->ajaxReturn(Code::SUCCESS, '登录成功', ["name" => $user["name"], "id" => $user["id"], "roles" => $roles], $jwt);
    }

    public function test() {
        $data = Db::table("engineer")->field("id as engineer_id, contact_qq as qq, contact_phone as phone")->limit(500*(8-1), 500)->select()->toArray();
        foreach ($data as $k => $v) {
            $data[$k]["password"] = password_hash("123456", PASSWORD_DEFAULT);
        }
        $res = (new UserEngineersMapper())->addAll($data);
        if (!$res)
            echo "失败";
        else
            echo "成功";
    }
}
