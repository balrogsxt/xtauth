<?php

session_start();

include_once 'config.php';
include_once 'lib/XtAuth.php';

$XtAuth = new XtAuth(XT_APPID,XT_APPKEY,XT_CALLBACK);
$access_token = $XtAuth->getAccessToken();
if($access_token==null){
	header('location:index.php');//没有授权,先授权登录
}
if(isset($_GET['exit'])){
	$XtAuth->quit();
	header('location:index.php');//没有授权,先授权登录
}
//php获取方法
$data = $XtAuth->get('user',[
	'access_token'=>$access_token,
	'scope'=>'id,user,name,sex,level,exp,coin,avatar,ban,site,email,group,sexid'
]);
$data = json_decode($data,true);
$data = $data['data'];
?>


<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>七空幻音用户数据</title>
	<script src="https://cdn.bootcss.com/vue/2.3.0/vue.min.js"></script>
	<script src="https://cdn.bootcss.com/axios/0.16.1/axios.min.js"></script>
</head>
<body>

<div style="float:left;width:500px;">
	<h1>php获取数据</h1>
	<ul>
		<li><img src="<?=$data['avatar']?>"></li>
		<li>ID:<?=$data['id']?></li>
		<li>账号:<?=$data['user']?></li>
		<li>昵称:<?=$data['name']?></li>
		<li>性别:<?=$data['sex']?></li>
		<li>站点:<?=$data['site']?></li>
		<li>等级:<?=$data['level']?></li>
		<li>幻币:<?=$data['coin']?></li>
		<li>经验:<?=$data['exp']?></li>
		<li>邮箱:<?=$data['email']?></li>
		<li>权限:<?=$data['group']?></li>
		<li>性别(数字):<?=$data['sexid']?></li>
		<li><a href="?exit=true">退出登录</a></li>
	</ul>
</div>
<div style="float:left;width:500px;">
	<h1>ajax获取数据</h1>
	<div id="user">
		<ul>
			<li><img :src="info.avatar"></li>
			<li>ID:{{info.id}}</li>
			<li>账号:{{info.user}}</li>
			<li>昵称:{{info.name}}</li>
			<li>性别:{{info.sex}}</li>
			<li>站点:{{info.site}}</li>
			<li>等级:{{info.level}}</li>
			<li>幻币:{{info.coin}}</li>
			<li>经验:{{info.exp}}</li>
			<li>邮箱:{{info.email}}</li>
			<li>权限:{{info.group}}</li>
			<li>性别(数字):{{info.sexid}}</li>
			<li><a href="?exit=true">退出登录</a></li>
		</ul>
	</div>
</div>

<script>
	new Vue({
		data:{
			info:{}
		},
		created:function(){
			var live = this;
			axios.get('http://api.acgxt.com/interfaces/user',{
				params:{
					access_token:'<?=$access_token?>',
					scope:'id,user,name,sex,level,exp,coin,avatar,ban,site,email,group,sexid'
				}
			}).then(function(req){
				live.info = req.data.data

			}).catch(function(e){
				alert('请求失败,请稍后再试:'+e.message);
			})
		}
	}).$mount('#user');
</script>
</body>
</html>