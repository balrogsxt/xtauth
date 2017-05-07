<?php
session_start();
ini_set("display_errors", "On");
include('../../../wp-load.php');
include_once('function.php');
$conf = xt_getAuthConfig();
$XtAuth = new XtAuth($conf['appid'],$conf['appkey'],$conf['callback']);
if(!isset($_REQUEST['sign'])||!isset($_REQUEST['code'])){
	sendErrorHtml('登录请求发生错误,请尝试重新登录');
}

$code = $_REQUEST['code'];
$sign = $_REQUEST['sign'];
if(!isset($_SESSION['access_sign'])||$sign!=$_SESSION['access_sign']){//验证是否2次请求为同一个地址
	sendErrorHtml('授权页面已失效,请尝试重新登录');
}
unset($_SESSION['access_sign']);
$result = $XtAuth->get_token($code);//获取access_token,以及其他用户基础数据
if(!$XtAuth->isJson($result)){
	sendErrorHtml('回调数据发生错误,请尝试重新登录');
}
$resultData = json_decode($result,true);
if($resultData['status']==1){
	sendErrorHtml("授权登录失败:错误原因:".$resultData['error']);
}
//登录成功,判断是否绑定
$data = $resultData['data'];
global $wpdb;
$bindUser = isBindXtAuth($data['id']);
if($bindUser!==false){
	xt_set_login($bindUser);
	header('location:'.admin_url());
	exit;
}
$_SESSION['xt_auth_verify'] = [
	'user'=>$data['user'],
	'name'=>$data['name'],
	'email'=>$data['email'],
	'site'=>$data['site'],
	'avatar'=>$data['avatar'],
	'id'=>$data['id']
];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>确认授权信息 - 七空幻音</title>
	<script src="https://cdn.bootcss.com/vue/2.3.2/vue.min.js"></script>
	<script src="https://cdn.bootcss.com/axios/0.16.1/axios.min.js"></script>
	<style>
		*{margin:0;padding:0;}
		body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,div
		{
			margin: 0;
			padding: 0;
			font-family: Microsoft YaHei, Helvetica Neue, Helvetica, Arial, sans-serif;
			color: #222;
			font-size: 13px;
			outline: none;
		}
		/*html5 e*/
		header,footer,aside,section{
			display:block;
		}
		*::selection {
			color: #48aec4;
			background: #fffff9;
		}

		*::-moz-selection {
			color: #48aec4;
			background: #fffff9;
		}

		img {
			border: none;
		}

		a {
			color: #222;
			text-decoration: none;
		}
		em {
			font-style: normal;
		}

		body {
			overflow:hidden;
			height:100%;
			width:100%;
			background-color: #eaedf1;
		}

		html {
			overflow:hidden;
			height:100%;
			width:100%;
		}

		li {
			list-style-type: none;
		}
		.warp{
			width:350px;
			border-radius:1px;
			padding:10px 20px;
			margin:200px auto 0;
			background-color:rgba(255,255,255,0.4);
			box-shadow:0 0 5px #ccc;
		}
		.warp h2{
			font-weight:600;
			text-align:center;
			font-size:16px;
			color:#555;
		}
		.warp .data{
			height:100px;
			margin-top:10px;
			overflow:hidden;
		}
		.warp .data img{
			width:80px;
			float:left;
			margin:10px;
			margin-right:20px;
			height:80px;
			border:1px solid #ccc;
			border-radius: 1px;
		}
		.warp .data ul li{
			height:30px;
			list-style:none;
			overflow:hidden;
			line-height:30px;
			margin-bottom:2px;
		}
		.warp .data ul li span{
			font-size:12px;
			float:left;
			width:40px;
			color:#555;
			height:30px;
			line-height:30px;
		}
		.warp .data ul li input.text{
			overflow:hidden;
			text-indent:2px;
			border:1px solid #d9ded6;
			height:18px;
			padding:2px 5px;
			width:150px;
			font-size:14px;
			color:#777;
			box-shadow:0 0 2px #e8e8e8 inset;
		}
		.warp .data ul li input:focus{
			border:1px solid #4f9cdd;
			box-shadow:0 0 2px #4f9cdd inset;
		}
		.warp .action{
			overflow:hidden;
			margin:10px 0;
		}
		.warp .action a{
			border:none;
			cursor:pointer;
			padding:4px 15px;
			height:24px;
			font-size:12px;
			line-height:24px;
			background-color: #229aeb;
			color:#fff;
			transition:.3s;
			float:left;
			margin-right:25px;
		}

		.warp .action a:hover
		{
			background-color: #0085af;
		}
		.warp .alert{
			position:absolute;
			top:0;
			left:0;
			width:100%;
			height:100%;
			background-color:rgba(0,0,0,0.5);
		}
		.warp .alert .alert_data{
			width:250px;
			background-color:#fff;
			box-shadow:0 0 5px #ccc;
			position:fixed;
			top:50%;
			left:50%;
			margin-left:-125px;
			margin-top:-200px;
			padding:5px 10px;
		}
		.warp .alert .alert_data h4{
			font-size:12px;
			font-weight:bold;
			color: #484848;
		}
		.warp .alert .alert_data p{
			height:70px;
			font-size:13px;
			color:#666;
			padding-top:5px;
		}
		.warp .alert .alert_data .act{
			text-align:center;
			padding-bottom:10px;
		}
		.warp .alert .alert_data .act a{
			border:none;
			cursor:pointer;
			padding:7px 30px;
			height:24px;
			font-size:12px;
			line-height:24px;
			background-color: #229aeb;
			color:#fff;
			transition:.3s;
			margin:0 5px;
		}
		.fade-enter-active, .fade-leave-active {
			transition: opacity .1s
		}
		.fade-enter, .fade-leave-active {
			opacity: 0
		}
		.warp .data h2{
			text-align:left;
		}
		.warp .action a.bind{
			border:none;
			cursor:pointer;
			padding:4px 15px;
			height:24px;
			font-size:12px;
			text-align:center;
			line-height:24px;
			display:block;
			background-color: #ffae25;
			color:#fff;
			transition:.3s;
		}
		.warp .action a.bind:hover{
			background-color: #e5ac4e;

		}
	</style>
</head>
<body style="background:url('<?php echo $conf['background'];?>') no-repeat;background-size:cover;">
<div class="warp" id="app">
	<h2>授权成功,请选择创建账号或关联已有用户</h2>
	<div class="data" v-show="type==1">
		<img src="http://i0.hdslb.com/bfs/face/125e9b3db5cebdac40c98607e5f6980832eb9050.jpg" alt="授权头像">
		<ul>
			<li>
				<span>账号:</span>
				<input type="text" class="text" autocomplete="off" v-model="info.user">
			</li>
			<li>
				<span>昵称:</span>
				<input type="text" class="text" autocomplete="off" v-model="info.name">
			</li>
			<li>
				<span>邮箱:</span>
				<input type="email" class="text" autocomplete="off" v-model="info.email">
			</li>
		</ul>
	</div>
	<div class="data" v-show="type==2">
		<ul style="margin:20px auto;width:240px;">
			<li style="margin-bottom:10px;">
				<span>账号:</span>
				<input type="text"  class="text" autocomplete="off" v-model="bind.user">
			</li>
			<li>
				<span>密码:</span>
				<input type="password" class="text" autocomplete="off" v-model="bind.pwd">
			</li>
			<li>
			</li>
		</ul>
	</div>
	<div class="action">
		<a href="javascript:;" @click="show" v-show="type==1">使用授权信息创建用户</a>
		<a href="javascript:;" @click="setType(2)" v-show="type==1">绑定网站已有账号</a>
		<a href="javascript:;" class="bind" @click="bindUser" v-show="type==2">开始绑定账号</a>
		<a href="javascript:;" @click="setType(1)" v-show="type==2">返回授权信息创建</a>
	</div>
	<transition name="fade">
		<div class="alert" v-show="isAlert">
			<div class="alert_data">
				<h4>提示</h4>
				<p>你确定要将授权获取信息作为你在这个网站新的信息吗!</p>
				<div class="act">
					<a href="javascript:;" @click="createUser">确认</a>
					<a href="javascript:;" @click="hide" style="background-color:#6b6b6b;">取消</a>
				</div>
			</div>
		</div>
	</transition>
	<transition name="fade">
		<div class="alert" v-show="isShow">
			<div class="alert_data">
				<h4>提示</h4>
				<p>{{alertmsg}}</p>
				<div class="act">
					<a href="javascript:;" @click="isShow=false" v-show="!success">确认</a>
					<a href="<?php echo admin_url();?>" v-show="success">完成</a>
				</div>
			</div>
		</div>
	</transition>
</div>
<script>
	new Vue({
		data:{
			type:1,
			info:{
				user:'<?php echo $data['user']?>',
				name:'<?php echo $data['name']?>',
				email:'<?php echo $data['email']?>'
			},
			bind:{
				user:'',
				pwd:''
			},
			alertmsg:'',
			isAlert:false,
			isShow:false,
			success:false
		},
		methods:{
			createUser:function(){
				this.hide();
				var user = this.info.user,
					name = this.info.name,
					email = this.info.email;
				if(user.length==0){
					this.alertmsg = '授权用户账号为空,无法创建';
					this.isShow = true;
					return;
				}
				if(name.length==0){
					this.alertmsg = '授权用户名称为空,无法创建';
					this.isShow = true;
					return;
				}
				var live = this;
				var url = '<?php echo plugins_url('verify.php',__FILE__)?>';
				axios({
					url: url,
					method: 'post',
					data: {
						type:'create',
						user: user,
						name:name,
						email:email
					},
					transformRequest: [function (data) {
						let ret = '';
						for (let it in data) {
							ret += encodeURIComponent(it) + '=' + encodeURIComponent(data[it]) + '&'
						}
						return ret
					}],
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function(r){
					var data = r.data;
					if(data.status==0){
						live.alertmsg = '创建新用户成功!';
						live.isShow = true;
						live.success = true;
					}else{
						live.alertmsg = '创建失败:'+data.message;
						live.isShow = true;
					}
				}).catch(function(e){
					live.alertmsg = '当前无法创建用户,请稍后再试';
					live.isShow = true;
				});

			},
			setType(type){
				this.type = type;
			},
			bindUser:function(){
				var user = this.bind.user,
					pwd = this.bind.pwd;
				if(user.length==0){
					this.alertmsg = '账号不能为空';
					this.isShow = true;
					return;
				}
				if(pwd.length==0){
					this.alertmsg = '密码不能为空';
					this.isShow = true;
					return;
				}
				var live = this;
				var url = '<?php echo plugins_url('verify.php',__FILE__)?>';
				axios({
					url: url,
					method: 'post',
					data: {
						type:'bind',
						user: user,
						pwd:pwd
					},
					transformRequest: [function (data) {
						let ret = '';
						for (let it in data) {
							ret += encodeURIComponent(it) + '=' + encodeURIComponent(data[it]) + '&'
						}
						return ret
					}],
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function(r){
					var data = r.data;
					if(data.status==0){
						live.alertmsg = '绑定账号成功!';
						live.isShow = true;
						live.success = true;
					}else{
						live.alertmsg = '绑定失败:'+data.message;
						live.isShow = true;
					}
				}).catch(function(e){
					live.alertmsg = '当前无法绑定账号,请稍后再试';
					live.isShow = true;
				});
			},
			hide:function(){
				this.isAlert = false;
			},
			show:function(){
				this.isAlert = true;
			}
		}


	}).$mount('#app');
</script>
</body>
</html>