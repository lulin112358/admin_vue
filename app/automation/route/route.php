<?php

use app\automation\controller\Index;
use app\automation\controller\Login;
use app\automation\controller\Order;
use app\middleware\automation\CheckSign;
use think\facade\Route;

Route::group('automation', function () {
    Route::post("engineer/login", Login::class."@login");
    Route::get("user/info", Index::class."@getUserInfo");
    Route::post("order/info", Order::class."@getOrderInfo");
    Route::get("engineer", Index::class."@searchEngineer");
    Route::post("order/split", Order::class."@splitOrder");
    Route::put("order", Order::class."@updateOrder");
    Route::get("orderid", Order::class."@getOrderIdByOrderSn");
    Route::put("engineer/pwd", Index::class."@updatePassword");
    Route::put("order/status", Order::class."@updateOrderStatus");
    Route::get("engineer/manuscript", Index::class."@manuscript");
    Route::get("manuscript/detail", Index::class."@manuscriptDetail");
})->allowCrossDomain()->middleware([CheckSign::class]);
