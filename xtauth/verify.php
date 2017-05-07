<?php

session_start();
ini_set("display_errors", "On");
include('../../../wp-load.php');

xt_createField();
if(!isset($_SESSION['xt_auth_verify'])){
	exit(json_encode(['status'=>1,'message'=>'null','err'=>1]));
}
$data = $_SESSION['xt_auth_verify'];
if(!is_array($data)){
	exit(json_encode(['status'=>1,'message'=>'验证信息不正确','err'=>2]));
}
if(!isset($data['id'])){
	exit(json_encode(['status'=>1,'message'=>'数据错误','err'=>3]));
}

if(isset($_POST['type'])){

	$type = $_POST['type'];
	if($type!='create'&&$type!='bind'){
		exit(json_encode(['status'=>1,'message'=>'选择类型不正确','err'=>4]));
	}
	if(!isset($_POST['user'])){
		exit(json_encode(['status'=>1,'message'=>'请填写用户账号','err'=>7]));
	}
	$u = $_POST['user'];
	if(strlen(trim($u))==0||strlen($u)>30){
		exit(json_encode(['status'=>1,'message'=>'账号过短或太长','err'=>8]));
	}
	global $wpdb;
	$ud = $wpdb->get_row("SELECT xt_uid FROM $wpdb->users WHERE user_login = '{$u}'");
	if($ud->xt_uid!=0){
		exit(json_encode(['status'=>1,'message'=>'该账号已被绑定','err'=>11]));
	}
	if($type=='create'){
		if(!isset($_POST['user'])||!isset($_POST['name'])||!isset($_POST['email'])){
			exit(json_encode(['status'=>1,'message'=>'请完整填写创建信息','err'=>5]));
		}
		$user = $_POST['user'];
		$name = $_POST['name'];
		$email = $_POST['email'];
		if(strlen(trim($name))==0||strlen($name)>30){
			exit(json_encode(['status'=>1,'message'=>'名称过短或太长','err'=>12]));
		}
		if(!is_email($email)){
			exit(json_encode(['status'=>1,'message'=>'邮箱格式不正确','err'=>13]));
		}
		$site = isset($data['site'])?$data['site']:'';
		$id = wp_insert_user([
			'user_pass'=>wp_hash_password(time().$user.rand(0,99999)),
			'user_login'=>$user,
			'user_nicename'=>$name,
			'display_name'=>$name,
			'nickname'=>$name,
			'user_registered'=>date('Y-m-d H:i:s'),
			'user_url'=>$site,
			'user_email'=>$email
		]);
		if(is_wp_error($id)){
			exit(json_encode(['status'=>1,'message'=>$id->get_error_message(),'err'=>10]));
		}

		$q = $wpdb->query("update `{$wpdb->users}` set xt_uid = '{$data['id']}',xt_avatar='{$data['avatar']}' where ID = '$id'");
		if($q){
			xt_set_login($user);
			unset($_SESSION['xt_auth_verify']);
			exit(json_encode(['status'=>0]));
		}else{
			exit(json_encode(['status'=>1,'message'=>'更新用户数据失败,请稍后再试','err'=>21]));
		}

	}else{
		if(!isset($_POST['user'])||!isset($_POST['pwd'])){
			exit(json_encode(['status'=>1,'message'=>'账号或密码不能为空','err'=>9]));
		}
		$user = $_POST['user'];
		$pass = $_POST['pwd'];
		//判断是否绑定
		if(strlen(trim($user))==0){
			exit(json_encode(['status'=>1,'message'=>'账号不能为空','err'=>14]));
		}
		if(strlen(trim($pass))==0){
			exit(json_encode(['status'=>1,'message'=>'密码不能为空','err'=>14]));
		}
		$id = xt_checkPassword($user,$pass);
		if($id==0){
			exit(json_encode(['status'=>1,'message'=>'账号或密码错误','err'=>10]));
		}
		//绑定
		$q = $wpdb->query("update `{$wpdb->users}` set xt_uid = '{$data['id']}',xt_avatar='{$data['avatar']}' where ID = '$id'");
		if($q){
			xt_set_login($user);
			unset($_SESSION['xt_auth_verify']);
			exit(json_encode(['status'=>0]));
		}else{
			exit(json_encode(['status'=>1,'message'=>'绑定用户失败,请稍后再试','err'=>21]));
		}
	}
}else{
	exit(json_encode(['status'=>1,'message'=>'类型错误','err'=>20]));
}

