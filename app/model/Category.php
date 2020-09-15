<?php


namespace app\model;


use think\Model;

class Category extends Model
{
    # 定义表名
    protected $table = "category";
    # 定义主键
    protected $pk = "id";
}