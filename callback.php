<?php
session_start();

include_once 'config.php';
include_once 'lib/XtAuth.php';

$XtAuth = new XtAuth(XT_APPID,XT_APPKEY,XT_CALLBACK);
if($XtAuth->isLogin()){
	header("location:user.php");
}
if(!isset($_REQUEST['sign'])||!isset($_REQUEST['code'])){
	exit('回调数据残缺');
}

$code = $_REQUEST['code'];
$sign = $_REQUEST['sign'];
if(!isset($_SESSION['access_sign'])||$sign!=$_SESSION['access_sign']){//验证是否2次请求为同一个地址
	exit('回调数据不统一');
}
unset($_SESSION['access_sign']);
$result = $XtAuth->get_token($code);//获取access_token,以及其他用户基础数据
if(!$XtAuth->isJson($result)){
	exit('回调数据错误'.$result);
}
$resultData = json_decode($result,true);
if($resultData['status']==1){
	exit("授权登录失败:错误原因:".$resultData['error']."[ErrorCode:{$resultData['err']}]");
}
$XtAuth->saveAccessToken($resultData['access_token']);

//授权成功
$accessToken = $resultData['access_token'];//access_token获取
$id = $resultData['data']['id'];//用户id
$user = $resultData['data']['user'];//用户账号
$name = $resultData['data']['name'];//用户昵称
$avatar = $resultData['data']['avatar'];//用户头像
$sex = $resultData['data']['sex'];//用户性别
$site = $resultData['data']['site'];//用户个人站点
?>
<div>
	<img src="<?=$avatar?>">
	<h1><?="{$user}({$name})已授权登录成功"?></h1>
	<a href="user.php">用户数据</a>
</div>
