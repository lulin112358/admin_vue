<?php

use app\automation\controller\Index;
use app\automation\controller\Login;
use app\middleware\automation\CheckSign;
use think\facade\Route;

Route::group('automation', function () {
    Route::post("engineer/login", Login::class."@login");
    Route::get("user/info", Index::class."@getUserInfo");
    Route::get("order/info", Index::class."@getOrderInfo");
})->allowCrossDomain()->middleware([CheckSign::class]);
