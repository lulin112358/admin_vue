<?php

use app\admin\controller\Account;
use app\admin\controller\AmountAccount;
use app\admin\controller\AuthFields;
use app\admin\controller\Category;
use app\admin\controller\Degree;
use app\admin\controller\Engineer;
use app\admin\controller\Evaluation;
use app\admin\controller\GroupChat;
use app\admin\controller\ManuscriptFee;
use app\admin\controller\Orders;
use app\admin\controller\OrdersDeposit;
use app\admin\controller\OrdersFinalPayment;
use app\admin\controller\Origin;
use app\admin\controller\Profession;
use app\admin\controller\RoleAuthFields;
use app\admin\controller\RoleAuthRow;
use app\admin\controller\School;
use app\admin\controller\SettlementLog;
use app\admin\controller\Software;
use app\admin\controller\Tendency;
use app\admin\controller\Upload;
use app\admin\controller\UserAuthFields;
use app\admin\controller\UserAuthRow;
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
    Route::get('user/user_manager', User::class.'@managerUser');
    Route::get('user/customer_user', User::class.'@customerUser');
    Route::get('user/biller_user', User::class.'@billerUser');
    Route::get('user/almighty_user', User::class.'@almightyUser');
    Route::get('user/partner_user', User::class.'@partnerUser');
    Route::get('user/part_time_editor', User::class.'@partTimeEditor');
    Route::get('user/all_group', User::class.'@allGroupUsers');
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

    # 群
    Route::get("group_chat", GroupChat::class."@groupChat");
    Route::get("group_chat/info", GroupChat::class."@groupChatInfo");
    Route::post("group_chat", GroupChat::class."@addGroupChat");
    Route::put("group_chat", GroupChat::class."@updateGroupChat");
    Route::delete("group_chat", GroupChat::class."@delGroupChat");

    # 学位
    Route::get("degree", Degree::class."@degree");
    Route::get("degree/info", Degree::class."@degreeInfo");
    Route::post("degree", Degree::class."@addDegree");
    Route::put("degree", Degree::class."@updateDegree");
    Route::delete("degree", Degree::class."@delDegree");

    # 擅长软件
    Route::get("software", Software::class."@software");
    Route::get("software/info", Software::class."@softwareInfo");
    Route::post("software", Software::class."@addSoftware");
    Route::put("software", Software::class."@updateSoftware");
    Route::delete("software", Software::class."@delSoftware");

    # 倾向类型
    Route::get("tendency", Tendency::class."@tendency");
    Route::get("tendency/info", Tendency::class."@tendencyInfo");
    Route::post("tendency", Tendency::class."@addTendency");
    Route::put("tendency", Tendency::class."@updateTendency");
    Route::delete("tendency", Tendency::class."@delTendency");

    # 评价
    Route::get("evaluation", Evaluation::class."@evaluation");
    Route::get("evaluation/info", Evaluation::class."@evaluationInfo");
    Route::post("evaluation", Evaluation::class."@addEvaluation");
    Route::put("evaluation", Evaluation::class."@updateEvaluation");
    Route::delete("evaluation", Evaluation::class."@delEvaluation");

    # 业务类型
    Route::get("category", Category::class."@list");
    Route::get("category/text", Category::class."@listText");
    Route::get("category/info", Category::class."@categoryInfo");
    Route::post("category", Category::class."@add");
    Route::put("category", Category::class."@update");
    Route::delete("category", Category::class."@del");

    # 工程师
    Route::get("engineer", Engineer::class."@engineer");
    Route::get("engineers_base", Engineer::class."@engineerBaseInfo");
    Route::post("engineer", Engineer::class."@addEngineer");
    Route::delete("engineer", Engineer::class."@delEngineer");
    Route::put("engineer", Engineer::class."@updateEngineer");

    # 订单
    Route::get("orders", Orders::class."@orders");
    Route::get("order", Orders::class."@order");
    Route::get("orders/auto_fill", Orders::class."@ordersAutoFill");
    Route::post("orders", Orders::class."@addOrder");
    Route::put("orders", Orders::class."@updateOrder");
    Route::delete("orders", Orders::class."@delOrder");
    Route::delete("orders/engineer", Orders::class."@delEngineer");
    Route::post("orders/split", Orders::class."@splitOrder");

    # 定金
    Route::get("deposit", OrdersDeposit::class."@deposit");

    # 尾款
    Route::get("final_payment", OrdersFinalPayment::class."@finalPayment");

    # 权限列管理
    Route::get("auth_field", AuthFields::class."@authFields");
    Route::get("user_auth_fields", UserAuthFields::class."@userAuthFields");
    Route::post("user_auth_fields", UserAuthFields::class."@assignUserAuthFields");
    Route::get("fields", AuthFields::class."@fieldsList");
    Route::get("role_auth_field/info", RoleAuthFields::class."@roleAuthFieldInfo");
    Route::get("auth_field/info", AuthFields::class."@authFieldInfo");
    Route::post("auth_field", AuthFields::class."@addAuthField");
    Route::post("role_auth_field", RoleAuthFields::class."@assignRoleAuthField");
    Route::put("auth_field", AuthFields::class."@updateAuthField");
    Route::delete("auth_field", AuthFields::class."@delAuthField");

    # 权限行管理
    Route::get("role_auth_row/info", RoleAuthRow::class."@roleAuthRowInfo");
    Route::get("user_auth_row/info", UserAuthRow::class."@userAuthRowInfo");
    Route::post("role_auth_row", RoleAuthRow::class."@assignRoleAuthRow");
    Route::post("user_auth_row", UserAuthRow::class."@assignUserAuthRow");

    # 专业
    Route::get("profession", Profession::class."@professions");

    # 学校
    Route::get("school", School::class."@schools");
    Route::get("search/school", School::class."@searchSchools");
    Route::get("school/top", Orders::class."@topSchool");

    # 稿费计算
    Route::get("manuscript_fee", ManuscriptFee::class."@manuscriptFees");
    Route::get("manuscript_fee/detail", ManuscriptFee::class."@engineerDetail");
    Route::post("settlement_all", ManuscriptFee::class."@settlementAll");
    Route::get("settlement/log", SettlementLog::class."@settlementLogs");
})->allowCrossDomain()->middleware([JwtMiddleware::class]);

# 后台路由组   不需要jwt登录验证
Route::group('admin', function () {
    Route::post('login', Login::class."@login");
    Route::post("orders/export", Orders::class."@export");
    Route::post("engineer/export", Engineer::class."@export");
    Route::post("manuscript_fee/export", ManuscriptFee::class."@export");
    Route::post("manuscript_fee/export_detail", ManuscriptFee::class."@exportDetail");
    Route::post("settlement_log/export", SettlementLog::class."@export");
    # 文件上传
    Route::post("upload", Upload::class."@upload");
})->allowCrossDomain();
