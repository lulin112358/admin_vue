<?php

use app\customer\controller\Degree;
use app\customer\controller\Engineer;
use app\customer\controller\Index;
use app\customer\controller\Order;
use app\customer\controller\Profession;
use app\customer\controller\School;
use app\customer\controller\Software;
use app\customer\controller\Tendency;
use app\customer\controller\Upload;
use think\facade\Route;

Route::group('customer', function () {
    Route::get("index", Index::class."@index");
    Route::get("degrees", Degree::class."@degrees");
    Route::get("schools", School::class."@schools");
    Route::get("professions", Profession::class."@professions");
    Route::get("software", Software::class."@software");
    Route::get("tendency", Tendency::class."@tendency");
    Route::post("engineer", Engineer::class."@addEngineer");
    Route::post("engineer/qrcode", Engineer::class."@updateEngineer");
    Route::get("order", Index::class."@order");
    Route::get("order/info", Order::class."@orderInfo");
    Route::get("qrcode", Index::class."@qrcode");
    Route::post("order/updateOrder", Order::class."@updateOrder");
    Route::post("upload", Upload::class."@upload");
})->allowCrossDomain();
