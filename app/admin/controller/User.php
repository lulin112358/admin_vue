<?php


namespace app\admin\controller;


use app\admin\service\UserRoleService;
use app\admin\service\UserService;
use app\Code;
use app\validate\UserRoleValidate;
use app\validate\UserValidate;
use think\facade\Validate;

class User extends Base
{
    /**
     * 获取用户/角色关联列表
     * @param UserRoleService $service
     */
    public function userList(UserRoleService $service) {
        try {
            $data = $service->getList();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 获取用户/角色信息
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function getUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("getOne")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $data = $service->getOne($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$data)
            $this->ajaxReturn(Code::ERROR, "暂无数据");

        $this->ajaxReturn($data);
    }

    /**
     * 添加用户
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function addUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("add")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->addUser($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (!$res)
            $this->ajaxReturn(Code::ERROR, "添加失败");

        $this->ajaxReturn(Code::SUCCESS, "添加成功");
    }

    /**
     * 修改密码
     * @param UserService $service
     * @param UserValidate $validate
     */
    public function updatePwd(UserService $service, UserValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("update_pwd")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        try {
            $res = $service->updatePwd($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        if (is_string($res))
            $this->ajaxReturn(Code::ERROR, $res);
        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "修改失败");
        $this->ajaxReturn("修改成功");
    }

    /**
     * 更新用户
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function updateUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        # 数据验证
        if (!$validate->scene("update")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());
        $va = Validate::rule([
            "user_name|账户名称" => "require|unique:user,user_name,{$param['user_id']},id"
        ]);
        if (!$va->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $va->getError());

        try {
            $res = $service->updateUser($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "更新失败");

        $this->ajaxReturn(Code::SUCCESS, "更新成功");
    }

    /**
     * 删除用户
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function delUser(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("del")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->delData($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "删除失败");

        $this->ajaxReturn(Code::SUCCESS, "删除成功");
    }

    /**
     * 更新用户状态
     * @param UserRoleService $service
     * @param UserRoleValidate $validate
     */
    public function updateStatus(UserRoleService $service, UserRoleValidate $validate) {
        $param = input("param.");
        if (!$validate->scene("upStatus")->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateStatus($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, "操作失败");

        $this->ajaxReturn(Code::SUCCESS, "操作成功");
    }

    /**
     * 获取管理层用户
     * @param UserRoleService $service
     */
    public function managerUser(UserService $service) {
        try {
            $data = $service->groupUsers(1);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 获取接单客服用户
     * @param UserService $service
     */
    public function customerUser(UserService $service) {
        try {
            $data = $service->groupUsers(5);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 获取发单人事用户
     * @param UserService $service
     */
    public function billerUser(UserService $service) {
        try {
            $data = $service->groupUsers(6);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 获取全能客服用户
     * @param UserService $service
     */
    public function almightyUser(UserService $service) {
        try {
            $data = $service->groupUsers(7);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 全能客服排序列表
     * @param UserService $service
     */
    public function almightyUserSort(UserService $service) {
        try {
            $data = $service->almightyUserSort();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取上游合作代理用户
     * @param UserService $service
     */
    public function partnerUser(UserService $service) {
        try {
            $data = $service->groupUsers(8);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 获取兼职编辑用户
     * @param UserService $service
     */
    public function partTimeEditor(UserService $service) {
        try {
            $data = $service->groupUsers(9);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        $this->ajaxReturn($data);
    }

    /**
     * 市场专员
     * @param UserService $service
     */
    public function commissioner(UserService $service) {
        try {
            $data = $service->groupUsers(10);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 市场经理
     * @param UserService $service
     */
    public function manager(UserService $service) {
        try {
            $data = $service->groupUsers(12);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 市场维护
     * @param UserService $service
     */
    public function maintain(UserService $service) {
        try {
            $data = $service->groupUsers(11);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }


    /**
     * 获取所有分组用户数据
     * @param UserService $service
     */
    public function allGroupUsers(UserService $service) {
        $param = input("param.");
        try {
            $data = $service->allGroupUsers($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取所有在职员工
     * @param UserService $service
     */
    public function getUsers(UserService $service) {
        try {
            $data = $service->selectBy(["status" => 1], "id, name");
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }
}
