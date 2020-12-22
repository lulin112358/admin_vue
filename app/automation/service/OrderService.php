<?php


namespace app\automation\service;


use app\BaseService;
use app\mapper\OrdersMapper;

class OrderService extends BaseService
{
    protected $mapper = OrdersMapper::class;
}
