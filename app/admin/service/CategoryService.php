<?php


namespace app\admin\service;


use app\mapper\CategoryMapper;
use app\model\Category;
use think\facade\Db;

class CategoryService extends BaseService
{
    protected $mapper = CategoryMapper::class;
}
