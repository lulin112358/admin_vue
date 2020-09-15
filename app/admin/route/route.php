<?php

use app\admin\controller\Category;
use think\facade\Route;
use app\admin\controller\Menu;
use app\admin\controller\Role;
use app\admin\controller\User;
use app\admin\controller\AccountCate;
use app\middleware\admin\JwtMiddleware;
use app\admin\controller\Login;

# 后台路由组   需要jwt登录验证
Route::group('admin', function () {
    # 菜单路由
    Route::get('menu', Menu::class.'@allMenu');
    Route::get('menuTree', Menu::class.'@allMenuTree');
    Route::post('menu', Menu::class.'@addMenu');
    Route::put('menu', Menu::class.'@updateMenu');
    Route::delete('menu', Menu::class.'@deleteMenu');
    Route::get('menu/one', Menu::class.'@getMenu');
    Route::put('menu/data', Menu::class.'@updateData');
    Route::get('menu/user', Menu::class.'@userMenu');

    # 角色路由
    Route::get('role', Role::class.'@allRole');
    Route::post('role', Role::class.'@addRole');
    Route::put('role', Role::class.'@updateRole');
    Route::delete('role', Role::class.'@delRole');
    Route::get('role/one', Role::class.'@getRole');
    Route::put('role/data', Role::class.'@updateData');
    Route::post('role/auth', Role::class.'@assignAuth');
    Route::get('role/roleAuth', Role::class.'@getRuleByRole');

    # 用户路由
    Route::get('user', User::class.'@userList');
    Route::post('user', User::class.'@addUser');
    Route::put('user', User::class.'@updateUser');
    Route::delete('user', User::class.'@delUser');
    Route::get('user/one', User::class.'@getUser');
    Route::put('user/status', User::class.'@updateStatus');
    # 获取市场来源人员选择信息
    Route::get('user/commissioner', User::class.'@commissioner');
    Route::get('user/manager', User::class.'@manager');
    Route::get('user/maintain', User::class.'@maintain');

    # 账号类型路由
//    Route::get('account_cate', AccountCate::class."@cateList");
//    Route::post('account_cate', AccountCate::class."@addCate");
//    Route::get('account_cate/one', AccountCate::class."@cateOne");
//    Route::put('account_cate', AccountCate::class."@updateCate");
//    Route::delete('account_cate', AccountCate::class."@delCate");

    # 业务类型
    Route::get("category", Category::class."@list");
    Route::post("category", Category::class."@add");
    Route::put("category", Category::class."@update");
    Route::delete("category", Category::class."@del");
})->allowCrossDomain()->middleware([JwtMiddleware::class]);

# 后台路由组   不需要jwt登录验证
Route::group('admin', function () {
    Route::post('login', Login::class."@login");
})->allowCrossDomain();