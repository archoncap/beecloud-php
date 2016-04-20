<?php
require_once("../loader.php");

$data = array();
$appSecret = APP_SECRET;
$data["app_id"] = APP_ID;
$data["timestamp"] = time() * 1000;
$data["app_sign"] = md5($data["app_id"] . $data["timestamp"] . $appSecret);
$data["limit"] = 10;
//退款单号
$refund_no = date('Ymd',time()).time() * 1000;

$type = $_GET['type'];
switch($type){
    case 'ALI' :
        $title = "支付宝";
        $data["channel"] = "ALI";
        break;
    case 'BD' :
        $title = "百度";
        $data["channel"] = "BD";
        break;
    case 'JD' :
        $title = "京东";
        $data["channel"] = "JD";
        break;
    case 'WX' :
        $title = "微信";
        $data["channel"] = "WX";
        break;
    case 'UN' :
        $title = "银联";
        $data["channel"] = "UN";
        break;
    case 'YEE' :
        $data["channel"] = "YEE";
        $title = "易宝";
        break;
    case 'KUAIQIAN' :
        $data["channel"] = "KUAIQIAN";
        $title = "快钱";
        break;
    case "PAYPAL" :
        $data["channel"] = "PAYPAL";
        $title = "PAYPAL";
        break;
    case "BC" :
        $data["channel"] = "BC";
        $title = "BC网关/快捷支付";
        break;
    default :
        exit("No this type.");
        break;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>BeeCloud<?php echo $title;?>订单查询示例</title>
</head>
<body>
<table border="1" align="center" cellspacing=0>
<?php
try {
    $result = $api->bills($data);
    if ($result->result_code != 0 || $result->result_msg != "OK") {
        echo json_encode($result->err_detail);
        exit();
    }
    $bills = $result->bills;
    $str = "<tr><td>ID</td><td>同意退款</td><td>是否支付</td><td>创建时间</td><td>总价(分)</td><td>渠道类型</td><td>订单号</td><td>订单标题</td></tr>";
    foreach($bills as $list) {
        $strParams = "?type=$type&refund_no=".$refund_no."&bill_no=".$list->bill_no."&refund_fee=".$list->total_fee;
        $agree_refund = $list->spay_result&&!$list->refund_result ? "<a href='agree.refund.php".$strParams."' target='_blank'>同意退款</a>" : "";
        $spay_result = $list->spay_result ? ($list->refund_result ? '已退款' : '支付') : '未支付';
        $create_time = $list->create_time ? date ( 'Y-m-d H:i:s', $list->create_time / 1000 ) : '';
        $str .= "<tr><td>$list->id</td><td>$agree_refund</td><td>$spay_result</td><td>$create_time</td><td>{$list->total_fee}</td><td>{$list->sub_channel}</td>
            	<td>{$list->bill_no}</td><td>{$list->title}</td></tr>";
    }
    echo $str;

    unset($data["limit"]);
    $result = $api->bills_count($data);
    if ($result->result_code != 0 || $result->result_msg != "OK") {
        echo json_encode($result->err_detail);
        exit();
    }
    $count = $result->count;
    echo '<tr><td colspan="2">支付订单总数:</td><td colspan="6">'.$count.'</td></tr>';
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
</table>
</body>
</html>