<?php


namespace app\admin\service;


use app\mapper\UserMapper;

class UserService extends BaseService
{
    protected $mapper = UserMapper::class;
}
