<?Php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Bangkok');

include('simple_html_dom.php');

function strToHex($string){
	$hex = '';
	if (isset($_GET['hex'])) {
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= "\x" . substr('0'.$hexCode, -2);
		}
	} else {
		$hex = $string;
	}
	return $hex;
}

function exitJson($obj) {
	exit(json_encode($obj));
}

function err($msg) {
	exitJson([
		'e' => true, 
		'msg' => strToHex($msg)
	]);
}

if (!isset($_POST['number'])) err('ไม่พบข้อมูล number');
if (strlen($_POST['number']) != 6) err('number ต้องมี 6 ตัวอักษร');
$number = (String)$_POST['number'];
$date = '';
if (isset($_POST['date'])) {
	$date = $_POST['date'];
} else {
	$date = date('Y-m-') . (date('m') == 1 ? '17' : '16');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['ldate' => $date, 'lnumber' => $number]);
curl_setopt($ch, CURLOPT_URL, 'http://www.glo.or.th/glo_seize/lottary/check_lottary.php');
$data = curl_exec($ch);
curl_close($ch);

$html = str_get_html($data);

$number = trim($html->find('td.checkLotto > span.GreenBold', 0)->plaintext);
$result = trim($html->find('td.checkLotto > span', 1)->plaintext);
$result = strpos('ไม่ถูกรางวัล') !== false ? -1 : $result;
exitJson([
	'e' => false, 
	'date' => $date,
	'number' => $number,
	'result' => $result !== -1 ? strToHex($result) : -1
]);
