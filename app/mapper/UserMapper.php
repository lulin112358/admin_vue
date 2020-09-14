<?php


namespace app\mapper;


use app\model\User;
use app\model\UserRole;

class UserMapper
{
    /**
     * 根据用户名查找用户信息
     * @param $data
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserByName($data) {
        return User::where(['user_name' => $data['user_name']])->find();
    }

    /**
     * 新增用户
     * @param $data
     * @return User|\think\Model
     */
    public function addUser($data) {
        return User::create($data);
    }

    /**
     * 更新用户信息
     * @param $data
     * @return User
     */
    public function updateUser($data) {
        return User::update($data);
    }

    /**
     * 删除用户信息
     * @param $ids
     * @return bool
     */
    public function delUser($ids) {
        return User::destroy($ids);
    }

    /**
     * 市场专员
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function commissioner() {
        return User::field("id, name")->select();
    }

    /**
     * 市场经理
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manager() {
        return User::field("id, name")->select();
    }

    /**
     * 市场维护
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function maintain() {
        return User::field("id, name")->select();
    }
}