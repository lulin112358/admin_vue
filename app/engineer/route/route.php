<?php

use app\engineer\controller\Index;
use app\engineer\controller\Manuscript;
use app\engineer\controller\Orders;
use app\middleware\admin\JwtMiddleware;
use think\facade\Route;

Route::group('engineer', function () {
    Route::put("update_pwd", Index::class."@updatePassword");
    Route::get("manuscript_fee", Manuscript::class."@manuscriptFee");
    Route::get("orders", Orders::class."@orders");
    Route::get("down/doc_list", Orders::class."@docList");
    Route::put("manuscript_fee", Manuscript::class."@confirmManuscript");
    Route::get("settlement/log", Manuscript::class."@settlementLog");
    Route::post("manuscript_fee/err", Manuscript::class."@errSubmit");
    Route::put("manuscript_fee/err_update", Manuscript::class."@errUpdate");
})->allowCrossDomain()->middleware([JwtMiddleware::class]);

Route::group("engineer", function () {
    Route::post("login", Index::class."@login");
    Route::post("manuscript_fee/export", Manuscript::class."@export");
    Route::post("manuscript_fee/export_log", Manuscript::class."@exportLog");
    Route::post("orders/export", Orders::class."@export");
    Route::get("orders/down_doc", Orders::class."@downDoc");
})->allowCrossDomain();
