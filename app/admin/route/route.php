<?php

use app\admin\controller\Account;
use app\admin\controller\AmountAccount;
use app\admin\controller\Attendance;
use app\admin\controller\AuthFields;
use app\admin\controller\Category;
use app\admin\controller\Crontab;
use app\admin\controller\CustomerBi;
use app\admin\controller\Degree;
use app\admin\controller\Engineer;
use app\admin\controller\Evaluation;
use app\admin\controller\GroupChat;
use app\admin\controller\IpWhite;
use app\admin\controller\ManuscriptFee;
use app\admin\controller\MarketBi;
use app\admin\controller\Orders;
use app\admin\controller\OrdersDeposit;
use app\admin\controller\OrdersFinalPayment;
use app\admin\controller\Origin;
use app\admin\controller\OriginBi;
use app\admin\controller\PartTime;
use app\admin\controller\Profession;
use app\admin\controller\Refund;
use app\admin\controller\RoleAuthFields;
use app\admin\controller\RoleAuthRow;
use app\admin\controller\School;
use app\admin\controller\Secrets;
use app\admin\controller\SettlementLog;
use app\admin\controller\Software;
use app\admin\controller\StationLetter;
use app\admin\controller\Tendency;
use app\admin\controller\Typesetting;
use app\admin\controller\Upload;
use app\admin\controller\UserAuthFields;
use app\admin\controller\UserAuthRow;
use app\admin\controller\UserExtend;
use app\admin\controller\Wechat;
use app\middleware\admin\IpFilter;
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
    Route::get('user/extend', UserExtend::class.'@userExtendInfo');
    Route::get('user/user_manager', User::class.'@managerUser');
    Route::get('user/customer_user', User::class.'@customerUser');
    Route::get('user/biller_user', User::class.'@billerUser');
    Route::get('user/almighty_user', User::class.'@almightyUser');
    Route::get('user/almighty_user_sort', User::class.'@almightyUserSort');
    Route::get('user/partner_user', User::class.'@partnerUser');
    Route::get('user/part_time_editor', User::class.'@partTimeEditor');
    Route::get('user/all_group', User::class.'@allGroupUsers');
    Route::post('user', User::class.'@addUser');
    Route::put('user', User::class.'@updateUser');
    Route::put('user/extend', UserExtend::class.'@updateUserExtendInfo');
    Route::delete('user', User::class.'@delUser');
    Route::get('user/one', User::class.'@getUser');
    Route::put('user/status', User::class.'@updateStatus');
    Route::put('user/update_pwd', User::class.'@updatePwd');
    # 获取市场来源人员选择信息
    Route::get('user/commissioner', User::class.'@commissioner');
    Route::get('user/manager', User::class.'@manager');
    Route::get('user/maintain', User::class.'@maintain');

    # 账号类型路由
    Route::get('account_cate', AccountCate::class."@cateList");
    Route::post('account_cate', AccountCate::class."@addCate");
    Route::get('account_cate/one', AccountCate::class."@cateOne");
    Route::put('account_cate', AccountCate::class."@updateCate");
    Route::delete('account_cate', AccountCate::class."@delCate");

    # 来源管理
    Route::post('origin', Origin::class."@addOrigin");
    Route::get('origin', Origin::class."@allOrigin");
    Route::get('origin/list', Origin::class."@originList");
    Route::get('origin/sort_list', Origin::class."@originSort");
    // Route::get('origin/info', Origin::class."@originInfo");
    Route::get('origin/info', Origin::class."@origin");
    Route::put('origin', Origin::class."@updateOrigin");
    Route::delete('origin', Origin::class."@delOrigin");

    # 接单账号
    Route::get('account', Account::class."@account");
    Route::get('account_sort', Account::class."@accountSort");
    Route::get('account/info', Account::class."@accountInfo");
    Route::post('account', Account::class."@addAccount");
    Route::put('account', Account::class."@updateAccount");
    Route::put('account/is_wechat', Account::class."@updateIsWechat");
    Route::delete('account', Account::class."@delAccount");

    # 收款账户
    Route::get("amount_account", AmountAccount::class."@account");
    Route::get("amount_account_sort", AmountAccount::class."@accountSort");
    Route::get("amount_account/info", AmountAccount::class."@accountInfo");
    Route::post("amount_account", AmountAccount::class."@addAccount");
    Route::put("amount_account", AmountAccount::class."@updateAccount");
    Route::delete("amount_account", AmountAccount::class."@delAccount");

    # 沉淀微信
    Route::get("wechat", Wechat::class."@wechat");
    Route::get("wechat_sort", Wechat::class."@wechatSort");
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
    Route::get("engineer/search", Engineer::class."@engineerSearch");
    Route::get("engineers_base", Engineer::class."@engineerBaseInfo");
    Route::get("engineer/aff_link", Engineer::class."@affLink");
    Route::post("engineer", Engineer::class."@addEngineer");
    Route::delete("engineer", Engineer::class."@delEngineer");
    Route::put("engineer", Engineer::class."@updateEngineer");

    # 订单
    Route::get("orders", Orders::class."@orders");
    Route::get("order", Orders::class."@order");
    Route::get("down/doc_list", Orders::class."@docList");
    Route::get("confirm_info", Orders::class."@confirmInfo");
    Route::get("orders/auto_fill", Orders::class."@ordersAutoFill");
    Route::post("orders", Orders::class."@addOrder");
    Route::put("orders", Orders::class."@updateOrder");
    Route::delete("orders", Orders::class."@delOrder");
    Route::delete("orders/engineer", Orders::class."@delEngineer");
    Route::post("orders/split", Orders::class."@splitOrder");
    Route::put("orders/bind_doc", Orders::class."@bindDoc");

    # 定金
    Route::get("deposit", OrdersDeposit::class."@deposit");

    # 尾款
    Route::get("final_payment", OrdersFinalPayment::class."@finalPayment");

    # 收款记录
    Route::get("payment_log", OrdersDeposit::class."@orderPaymentLog");
    Route::put("update_amount_account", OrdersDeposit::class."@updateDepositAccount");

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
    Route::get("manuscript_fee/can_settlement", ManuscriptFee::class."@canSettlement");
    Route::get("manuscript_fee/detail", ManuscriptFee::class."@engineerDetail");
    Route::get("manuscript_fee/can_settlement_detail", ManuscriptFee::class."@canSettlementDetail");
    Route::post("settlement_all", ManuscriptFee::class."@settlementAll");
    Route::post("direct_settlement", ManuscriptFee::class."@directSettlement");
    Route::get("settlement/log", SettlementLog::class."@settlementLogs");

    # 退款
    Route::get("refund", Refund::class."@refundList");
    Route::get("refund_log", Refund::class."@refundLogList");
    Route::post("refund", Refund::class."@refund");
    Route::post("refund_handle", Refund::class."@refundHandle");

    # 考勤
    Route::get("attendance", Attendance::class."@attendances");
    Route::get("attendance/user", Attendance::class."@userAttendances");
    Route::get("attendance/info", Attendance::class."@attendanceInfo");
    Route::put("attendance", Attendance::class."@updateAttendance");

    # IP
    Route::get("ip_white", IpWhite::class."@ipWhites");
    Route::get("ip_white/info", IpWhite::class."@ipInfo");
    Route::post("ip_white", IpWhite::class."@addIp");
    Route::put("ip_white", IpWhite::class."@updateIp");
    Route::delete("ip_white", IpWhite::class."@delIp");

    # 密钥
    Route::get("secrets", Secrets::class."@secrets");
    Route::get("secrets/info", Secrets::class."@secretInfo");
    Route::post("secrets", Secrets::class."@addSecret");
    Route::put("secrets", Secrets::class."@updateSecret");
    Route::delete("secrets", Secrets::class."@delSecret");

    # BI
    Route::get("customer_bi", CustomerBi::class."@customerBiCount");
    Route::get("customer_order", CustomerBi::class."@customerOrderBi");
    Route::get("customer_order/detail", CustomerBi::class."@cusOrderPerfDetailBi");
    Route::get("cus_order_perf", CustomerBi::class."@cusOrderPerfBi");
    Route::get("market_bi", MarketBi::class."@marketUserBi");
    Route::get("market_bi/detail", MarketBi::class."@marketUserOriginBi");
    Route::get("origin_bi", OriginBi::class."@originBi");
    Route::get("origin_bi/detail", OriginBi::class."@originDetailBi");
    Route::get("origin_bi/reconciliation", OriginBi::class."@originReconciliation");
    // Route::get("customer_bi/cols", CustomerBi::class."@customerBiCols");

    # 自动排版
    Route::get("typesetting", Typesetting::class."@schemaIdList");
    Route::post("typesetting", Typesetting::class."@uploadPaper");
    Route::get("typesetting/list", Typesetting::class."@recordList");
    Route::delete("typesetting", Typesetting::class."@delRecord");

    # 站内信
    Route::get("wait_deal", StationLetter::class."@waitDeal");
    Route::get("already_read", StationLetter::class."@alreadyRead");
    Route::get("already_deal", StationLetter::class."@alreadyDeal");
    Route::get("station_letters", StationLetter::class."@stationLetters");
    Route::put("deal_err", StationLetter::class."@dealErr");

    # 兼职管理
    Route::get("part_times", PartTime::class."@partTimes");
    Route::get("part_time_detail", PartTime::class."@partTimeDetail");
    Route::get("part_time_row", PartTime::class."@partTimeRow");
    Route::put("salary", PartTime::class."@updateSalary");
    Route::put("part_times", PartTime::class."@updatePartTime");
})->allowCrossDomain()->middleware([JwtMiddleware::class, IpFilter::class]);

# 后台路由组   不需要jwt登录验证
Route::group('admin', function () {
    Route::post('login', Login::class."@login");
//    Route::post('test', Login::class."@test");
    Route::post("orders/export", Orders::class."@export");
    Route::post("engineer/export", Engineer::class."@export");
    Route::post("manuscript_fee/export", ManuscriptFee::class."@export");
    Route::post("can_settlement/export", ManuscriptFee::class."@canSettlementExport");
    Route::post("manuscript_fee/export_detail", ManuscriptFee::class."@exportDetail");
    Route::post("can_settlement/export_detail", ManuscriptFee::class."@canSettlementDetailExport");
    Route::post("settlement_log/export", SettlementLog::class."@export");
    Route::post("refund/export", Refund::class."@exportRefund");
    Route::post("refund_log/export", Refund::class."@exportRefundLog");
    Route::post("cus_order_perf/export", CustomerBi::class."@export");
    Route::post("cus_order_perf_detail/export", CustomerBi::class."@exportDetail");
    Route::post("origin_bi/export", OriginBi::class."@export");
    Route::post("origin_detail_bi/export", OriginBi::class."@exportDetail");
    Route::post("origin_rec/export", OriginBi::class."@exportRec");
    Route::post("attendances/export", Attendance::class."@export");
    # 文件上传
    Route::post("upload", Upload::class."@upload");
    Route::post("upload_order_doc", Upload::class."@uploadOrderDoc");
    # 文档下载
    Route::get("orders/down_doc", Orders::class."@downDoc");
})->allowCrossDomain()->middleware([IpFilter::class]);

# 定时任务
Route::group("crontab", function () {
    Route::get("attendance", Crontab::class."@attendance");
    Route::get("typesetting/record", Typesetting::class."@recordFind");
});
