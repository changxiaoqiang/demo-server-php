<?php
include('DataBase.php'); //���ݲ�����
include('BaseType.php'); //������������
include('ProException.php'); //ִ���쳣
include('Process.php'); //�߼������ļ�

//��������ִ�е�function��keyΪ��������valueΪRequest method(GET/POST)
$allow_func  = array("login"=>"GET|POST", "reg"=>"GET|POST", "token"=>"GET|POST", "profile"=>"GET|POST", "friends"=>"GET|POST");
//���ݿ�����
define("DB_DNS","mysql:host=192.168.151.3;dbname=demoserver");
define("DB_USER","developer");
define("DB_PASSWORD","1234%^&*");

//����API��ַ
define("RONGCLOUD_API_URL","http://bj.rongcloud.net:9000/reg.json");
//����APP KEY
define("RONGCLOUD_APP_KEY","e0x9wycfx7flq");
//����APP SECRET
define("RONGCLOUD_APP_SECRET","TESTSECRET");

//��ȡfunction����
$func = substr($_SERVER["PHP_SELF"], strlen($_SERVER["URL"]) + 1);
//���Ҳ�ִ��function
if (isset($allow_func) && array_key_exists($func, $allow_func) && stripos($allow_func[$func], $_SERVER['REQUEST_METHOD'])!== false && function_exists($func)){
	$func_ref = new ReflectionFunction($func);
	$params = array(); 
	//�������
	foreach ($func_ref->getParameters() as $param) {
		$param_key = $param->getName();
		if (isset($_REQUEST[$param_key])) {
			if ($param->getClass() != null) {
				$param_class_name = $param->getClass()->getName();
				try {
					$param_class = new $param_class_name($_REQUEST[$param_key]);
				} catch(Exception $e) {
					header("status: 403 Forbidden");
					echo "$param_key is error";
					exit();
				}
				array_push($params, $param_class);
			} else {
				array_push($params, $_REQUEST[$param_key]);
			}
		} else {
			if ($param->isDefaultValueAvailable()) {
				array_push($params, $param->getDefaultValue());
			} else { 
				header("status: 403 Forbidden");
				echo "Missing $param_key parameter.";
				exit();
			}
		}
	}
	//ִ��function
	try {
		$result = call_user_func_array($func, $params);
	} 
	catch (ProException $pe) {
		echo json_encode(array("code" => $pe->getCode(), "message" => $pe->getMessage()));
		exit();
	}
	catch (Exception $e) {
		header("status: 500 Error");
		die("Error is " . $e->getMessage());
	}
	if (isset($result)) {
		echo json_encode(array("code" => 200,"result" => $result));
	} else {
		echo json_encode(array("code" => 200));
	}
} else {
	header("status: 404 Not Found");
	echo "Missing $func Function.";
}