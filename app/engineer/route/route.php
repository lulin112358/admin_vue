<?php

use app\engineer\controller\Index;
use app\engineer\controller\Manuscript;
use app\middleware\admin\JwtMiddleware;
use think\facade\Route;

Route::group('engineer', function () {
    Route::put("update_pwd", Index::class."@updatePassword");
    Route::get("manuscript_fee", Manuscript::class."@manuscriptFee");
})->allowCrossDomain()->middleware([JwtMiddleware::class]);

Route::group("engineer", function () {
    Route::post("login", Index::class."@login");
})->allowCrossDomain();
