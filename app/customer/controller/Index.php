<?php
declare (strict_types = 1);

namespace app\customer\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return view("index/index");
    }

    public function order() {
        return view("index/order");
    }
}
