<?php


namespace app\engineer\service;


use app\BaseService;
use app\mapper\OrderFilesMapper;
use app\mapper\OrdersMainMapper;
use app\mapper\OrdersMapper;
use app\mapper\UserRoleMapper;
use Carbon\Carbon;
use excel\Excel;
use jwt\Jwt;
use PhpZip\ZipFile;
use think\facade\Db;

class OrdersService extends BaseService
{
    protected $mapper = OrdersMapper::class;

    # 订单状态
    private $status = [
        1 => "未发出",
        2 => "已发出",
        3 => "已交稿",
        4 => "准备退款",
        5 => "已退款",
        6 => "已发全能",
        7 => "已发发单",
        8 => "返修中",
    ];

    # 订单状态颜色
    private $statusColor = [
        1 => "red",
        2 => "yellow",
        3 => "green",
        4 => "black",
        5 => "black",
        6 => "yellow",
        7 => "red",
        8 => "red",
    ];

    /**
     * 获取所有订单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orders($params, $export = false) {
        // 设置中文
        Carbon::setLocale("zh");
        $carbon = new Carbon();
        if ($export) {
            request()->uid = Jwt::decodeToken($params["token"])["data"]->uid;
        }
        # 构造字段查询条件
        $map = [];
        if (isset($params["search_fields"])) {
            foreach ($params["search_fields"] as $k => $v) {
                if (!$export) {
                    $val = json_decode($v, true);
                }else {
                    $val = explode(",", $v);
                }
                $map[$val[0]][] = $val[1];
            }
        }
        # 构造时间段查询条件
        $where = [];
        if (isset($params["date_time"]) && !empty($params["date_time"])) {
            if (strstr($params["search_order"], "create_time")) {
                $where[] = ["bill_time", ">=", strtotime($params["date_time"][0])];
                $where[] = ["bill_time", "<=", strtotime($params["date_time"][1])];
            }else if (strstr($params["search_order"], "delivery_time")){
                $where[] = ["delivery_time", ">=", strtotime($params["date_time"][0])];
                $where[] = ["delivery_time", "<=", strtotime($params["date_time"][1])];
            }
        }
        $searchKey = $params["search_key"]??"";
        # orders_view试图
        if (!$export) {
            $data = Db::table("orders_view")
                # 模糊匹配查询条件
                ->where("manuscript_fee|order_sn|require", "like", "%$searchKey%")
                ->where($where)
                ->where(["engineer_id" => request()->uid])
                ->field("create_time, order_sn, order_id, main_order_id, delivery_time, status, manuscript_fee, require")
                ->orderRaw("if(status=3, 1, 0), if(status=5, 1, 0)")
                ->order($params["search_order"])
                ->order("order_id asc")
                ->paginate(100, true)->items();
        }else {         # 导出excel不需要分页
            $data = Db::table("orders_view")
                # 模糊匹配查询条件
                ->where("manuscript_fee|order_sn|require", "like", "%$searchKey%")
                ->where($where)
                ->where(["engineer_id" => request()->uid])
                ->field("create_time, order_sn, order_id, main_order_id, delivery_time, status, manuscript_fee, require")
                ->orderRaw("if(status=3, 1, 0), if(status=5, 1, 0)")
                ->order($params["search_order"])
                ->order("order_id asc")->select()->toArray();
        }

        foreach ($data as $k => $v) {
//            $data[$k]["create_time"] = date("Y-m-d H:i:s", $v["create_time"]);
            $data[$k]["delivery_time"] = date("Y-m-d H", $v["delivery_time"]);
//            $data[$k]["commission_ratio"] = $v["commission_ratio"]<=1?($v["commission_ratio"] * 100)."%":$v["commission_ratio"]."元";
//            $data[$k]["biller"] = is_null($v["biller"])?"暂未填写":$v["biller"];
            $data[$k]["status"] = $this->status[$v["status"]];
//            $data[$k]["manuscript_fee_ratio"] = ($v["deposit"] + $v["final_payment"])==0?0:floatval(round(($v["manuscript_fee"] / ($v["deposit"] + $v["final_payment"])) * 100, 2));
//            $data[$k]["manuscript_fee_ratio"] = floatval(round($v["manuscript_fee_ratio"] * 100, 2))."%";
//            $data[$k]["build_time"] = Carbon::parse($data[$k]["create_time"])->diffForHumans(Carbon::now());
            # 保留有效位数
//            $data[$k]["total_amount"] = floatval($v["total_amount"]);
//            $data[$k]["total_fee"] = floatval($v["total_amount"]);
//            $data[$k]["deposit"] = floatval($v["deposit"]);
//            $data[$k]["refund_amount"] = is_null($v["refund_amount"])?"未退款":floatval($v["refund_amount"]);
//            $data[$k]["final_payment"] = floatval($v["final_payment"]);
            $data[$k]["manuscript_fee"] = floatval($v["manuscript_fee"]);
//            $data[$k]["check_fee"] = floatval($v["check_fee"]);
            # 消除分单后的总价/定金/尾款显示
//            $order_sn = explode("-", $v["order_sn"]);
//            if (count($order_sn) > 1) {
//                if ($order_sn[1] != "1") {
//                    $data[$k]["total_amount"] = "";
//                    $data[$k]["deposit"] = "";
//                    $data[$k]["final_payment"] = "";
//                }
//            }

//            $billTime = Carbon::parse(date("Y-m-d H:i:s", $v["bill_time"]));
//            $createTime = Carbon::parse($data[$k]["create_time"]);
//            # 分钟差
//            $billTimeMinutes = $createTime->diffInMinutes($billTime);
//            # 小时差
//            $billTimeHours = $createTime->diffInHours($billTime);
//            # 天数差
//            $billTimeDays = $createTime->diffInDays($billTime);
//            if ($billTimeHours > 24) {
//                $billTimeDiff = $billTimeDays."天".($billTimeHours - $billTimeDays * 24)."时".($billTimeMinutes - $billTimeHours * 60)."分";
//            }else if ($billTimeMinutes > 60) {
//                $billTimeDiff = $billTimeHours."时".($billTimeMinutes - $billTimeHours * 60)."分";
//            }else{
//                $billTimeDiff = $billTimeMinutes."分";
//            }
//            $data[$k]["bill_time"] = $v["bill_time"]==0?"未记录":$billTimeDiff;


            # TODO 此处待优化
            $time = Carbon::parse(date("Y-m-d H:i:s", $v["delivery_time"]));
            # 天数差
            $diffDay = $carbon->diffInDays($time);
            # 小时差
            $diffHour = $carbon->diffInHours($time);
            if ($diffHour > 24) {
                $diff = $diffDay."天".($diffHour - $diffDay * 24)."时";
            }else{
                $diff = $diffHour."时";
            }
            if (!$time->gt(Carbon::now())) {
                $diff = "超".$diff;
                $data[$k]["color"] = "red";
            }else{
                $rate = ($v["delivery_time"] - time()) / ($v["delivery_time"] - $v["create_time"]);
                if ($rate <= 0.3)
                    $data[$k]["color"] = "red";
                if ($rate <= 0.5 && $rate > 0.3)
                    $data[$k]["color"] = "yellow";
                if ($rate > 0.5)
                    $data[$k]["color"] = "green";
            }
            $data[$k]["status_color"] = $this->statusColor[$v["status"]];
            switch ($v["status"]) {
                case 3:
                    $diff = "已交稿";
                    $data[$k]["color"] = "black";
                    break;
                case 5:
                    $diff = "已退款";
                    $data[$k]["color"] = "black";
                    break;
                default:
                    break;
            }
            $data[$k]["countdown"] = $diff;
        }

        return $data;
    }

    /**
     * 导出
     * @param $param
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export($param) {
        $_data = $this->orders($param, true);
        $header = [
            ["订单编号", "order_sn"],
            ["要求", "require"],
            ["订单状态", "status"],
            ["稿费", "manuscript_fee"],
            ["交稿时间", "delivery_time"],
            ["倒计时", "countdown"],
        ];
        return Excel::exportData($_data, $header, "订单数据");
    }

    /**
     * 下载文档
     * @param $param
     * @throws \PhpZip\Exception\ZipException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function downDoc($param) {
        $zip = new ZipFile();

        $files = (new OrderFilesMapper())->downDoc(["of.id" => $param["id"]]);
//        $files = (new OrdersMainMapper())->downDoc(["om.id" => $param["main_order_id"]]);
        $downFileName = implode(",", array_unique(array_column($files, "order_sn"))).".zip";
        if (file_exists(root_path()."public/storage/doczips/".$downFileName)) {
            unlink(root_path()."public/storage/doczips/".$downFileName);
        }
        foreach ($files as $k => $v) {
//            $filename = empty($v["require"])?basename($v["filename"]):$v["require"].".".explode(".", basename($v["filename"]))[1];
            $filename = $v["filename"];
            $zip->addFile(root_path()."public/storage/".$v["file"], $v["order_sn"]."/".$filename);
//            if (!empty($v["file"])) {
//                foreach (explode(",", $v["file"]) as $key => $val) {
//                    $filename = empty($v["require"])?basename($val):$v["require"].".".explode(".", basename($val))[1];
//                    $zip->addFile(root_path()."public/storage/".$val, $v["order_sn"]."/".$filename);
//                }
//            }
        }
        if (empty($zip->getListFiles())) {
            throw new \Exception("暂无文档下载");
        }
        $zip->saveAsFile(root_path()."public/storage/doczips/".$downFileName)->close();
        download_file(root_path()."public/storage/doczips/".$downFileName, $downFileName);
    }
}
