<?php

use app\admin\controller\Account;
use app\admin\controller\AmountAccount;
use app\admin\controller\Category;
use app\admin\controller\Origin;
use app\admin\controller\Wechat;
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
    Route::get('account_cate', AccountCate::class."@cateList");
//    Route::post('account_cate', AccountCate::class."@addCate");
//    Route::get('account_cate/one', AccountCate::class."@cateOne");
//    Route::put('account_cate', AccountCate::class."@updateCate");
//    Route::delete('account_cate', AccountCate::class."@delCate");

    # 来源管理
    Route::post('origin', Origin::class."@addOrigin");
    Route::get('origin', Origin::class."@allOrigin");
    Route::get('origin/list', Origin::class."@originList");
    // Route::get('origin/info', Origin::class."@originInfo");
    Route::get('origin/info', Origin::class."@origin");
    Route::put('origin', Origin::class."@updateOrigin");
    Route::delete('origin', Origin::class."@delOrigin");

    # 接单账号
    Route::get('account', Account::class."@account");
    Route::get('account/info', Account::class."@accountInfo");
    Route::post('account', Account::class."@addAccount");
    Route::put('account', Account::class."@updateAccount");
    Route::delete('account', Account::class."@delAccount");

    # 收款账户
    Route::get("amount_account", AmountAccount::class."@account");
    Route::get("amount_account/info", AmountAccount::class."@accountInfo");
    Route::post("amount_account", AmountAccount::class."@addAccount");
    Route::put("amount_account", AmountAccount::class."@updateAccount");
    Route::delete("amount_account", AmountAccount::class."@delAccount");

    # 沉淀微信
    Route::get("wechat", Wechat::class."@wechat");
    Route::get("wechat/info", Wechat::class."@wechatInfo");
    Route::post("wechat", Wechat::class."@addWechat");
    Route::put("wechat", Wechat::class."@updateWechat");
    Route::delete("wechat", Wechat::class."@delWechat");

    # 业务类型
    Route::get("category", Category::class."@list");
    Route::get("category/text", Category::class."@listText");
    Route::get("category/info", Category::class."@categoryInfo");
    Route::post("category", Category::class."@add");
    Route::put("category", Category::class."@update");
    Route::delete("category", Category::class."@del");
})->allowCrossDomain()->middleware([JwtMiddleware::class]);

# 后台路由组   不需要jwt登录验证
Route::group('admin', function () {
    Route::post('login', Login::class."@login");
})->allowCrossDomain();
