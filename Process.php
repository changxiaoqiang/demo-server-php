<?php
/*
�û���¼
*/
function login(Email $email, $password){
	$db = new DataBase(DB_DNS, DB_USER, DB_PASSWORD);
	$user = $db->fetch("SELECT `id`, `email`, `username`, `portrait`, `password` FROM `user` WHERE `email` = ?", $email);
	if ($user) {
		if ($password != $user["password"]) {
			throw new ProException("password is error", 103);
		} else {
			// ����ʵ�֣��ݲ����ǰ�ȫ
			setcookie("user_id", $user['id'], time() + 36000);
			unset($user['password']);
			return $user;
		}
	} else {
		throw new ProException("user not found", 102);
	}
}

/*
�û�ע��
*/
function reg(Email $email, $username, $password){
	$db = new DataBase(DB_DNS, DB_USER, DB_PASSWORD);
	if ($db->fetchColumn("SELECT count(*) FROM `user` WHERE `email` = ?", $email) == 0) {
		$portrait = 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s=82&d=wavatar';
		$db->insert("INSERT INTO `user` (`email`, `username`, `portrait`, `password`) values(?,?,?,?)", $email, $username, $portrait, $password);
	} else {
		throw new ProException("$email is exists ", 101);
	}
}

/*
��ȡtoken
*/
function token(){
	$db = new DataBase(DB_DNS, DB_USER, DB_PASSWORD);
	$user = $db->fetch("SELECT `id`, `email`, `username`, `portrait` FROM `user` WHERE `id` = ?", $_COOKIE['user_id']);

	if($user) {
		$params = array('userId'=>$user['id'], 'name'=>$user['username'], 'portraitUri'=>$user['portrait']);
		$httpHeader = array('appKey:'.RONGCLOUD_APP_KEY,'appSecret:'.RONGCLOUD_APP_SECRET);
		$token = getToken($params, $httpHeader);
		if (!$token) {
			throw new Exception("API Server Error");
		}

		if ($token->code != 200) {
			throw new ProException($token->errorMessage, $token->code);
		} else {
			unset($token->code);
			return $token;
		}
	} else {
		throw new ProException("user not found", 102);
	}
}

/*
��ȡĳ���û�����
*/
function profile(Integer $id){
	$db = new DataBase(DB_DNS, DB_USER, DB_PASSWORD);
	$user = $db->fetch("SELECT `id`, `username`, `portrait` FROM `user` WHERE `id` = ?", $id);

	if($user) {
		return $user;
	} else {
		throw new ProException("user not found", 102);
	}
}

/*
��ȡȫ����������
*/
function friends(){
	$db = new DataBase(DB_DNS, DB_USER, DB_PASSWORD);
	return $db->fetchAll("SELECT `id`, `username`, `portrait` FROM `user`");
}

/*
������API�Ͻ����û���Ȩ
*/
function getToken($params,$httpHeader) {
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, RONGCLOUD_API_URL);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$ret = curl_exec($ch);
	if (false === $ret) {
		$err =  curl_errno($ch);
		curl_close($ch);
		return false;
	}
	curl_close($ch);
	return json_decode($ret);
}