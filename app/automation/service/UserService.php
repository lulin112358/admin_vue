<?php


namespace app\automation\service;


use app\BaseService;
use app\mapper\UserMapper;

class UserService extends BaseService
{
    protected $mapper = UserMapper::class;
}
