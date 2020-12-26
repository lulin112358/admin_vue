<?php

use app\automation\controller\Index;
use app\automation\controller\Login;
use app\automation\controller\Order;
use app\middleware\automation\CheckSign;
use think\facade\Route;

Route::group('automation', function () {
    Route::post("engineer/login", Login::class."@login");
    Route::get("user/info", Index::class."@getUserInfo");
    Route::get("order/info", Order::class."@getOrderInfo");
    Route::get("engineer", Index::class."@searchEngineer");
    Route::post("order/split", Order::class."@splitOrder");
    Route::put("order", Order::class."@updateOrder");
    Route::get("orderid", Order::class."@getOrderIdByOrderSn");
})->allowCrossDomain()->middleware([CheckSign::class]);
