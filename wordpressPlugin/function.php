<?php
include_once('lib/XtAuth.php');
function getBackground(){
	$background = get_option('xtauth_background');
	if(!$background){
		add_option("xtauth_background",'');
		$background = '';
	}
	return $background;
}
function xt_getAuthConfig(){
	$appid = get_option('xtauth_appid');
	if(!$appid){
		add_option("xtauth_appid");
		$appid = '';
	}
	$appkey = get_option('xtauth_appkey');
	if(!$appkey){
		add_option("xtauth_appkey");
		$appkey = '';
	}
	$callback = plugins_url('callback.php',__FILE__);
	$background = get_option('xtauth_background');
	if(!$background){
		add_option("xtauth_background",'');
		$background = '';
	}
	return [
		'appid'=>$appid,
		'appkey'=>$appkey,
		'callback'=>$callback,
		'background'=>$background
	];
}
function xt_checkPassword($user,$pass){
	global $wpdb;
	$id = $wpdb->get_var("select ID from {$wpdb->users} where user_login='{$user}'");
	if(wp_check_password($pass,md5($pass),$id)){
		return $id;
	}else{
		return 0;
	}
}
function isBindXtAuth($id){
	global $wpdb;
	$ud = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE xt_uid = '{$id}'");
	if(!$ud){
		return false;
	}
	if($ud->xt_uid!=0){
		return $ud->user_login;
	}else{
		return false;
	}
}
function sendErrorHtml($msg,$title=null,$url=null,$returnName='返回重新登录'){
	$url = is_null($url)?wp_login_url():$url;
	$title = is_null($title)?'授权出错 - '.$msg:$title;
	?>
	<!doctype html>
	<html><head><meta charset="UTF-8"><title><?php echo $title;?></title></head><body style="background:url('<?php echo getBackground()?>') no-repeat;background-size:cover;">
	<div style="width:300px;height:auto;background-color:rgba(255,255,255,0.3);box-shadow:0 0 5px #ccc;margin:200px auto;padding:5px 10px;">
		<h2 style="font-size:18px;font-weight:bold;color:#e82069;text-align:center;"><?php echo $msg;?></h2>
		<h3 style="text-align:center;"><a href="<?php echo $url;?>" style="text-decoration: none;color:#fff;background-color:#2f76f5;font-size:14px;text-align:center;padding:10px 20px;font-weight: 200;"><?php echo $returnName;?></a></h3>
	</div>
	</body>
	</html><?php
	unset($_SESSION['xt_auth_verify']);
	exit;
}
function xt_createField(){
	global $wpdb;
	$q = $wpdb->query("select xt_uid from $wpdb->users");
	if(!$q){
		$wpdb->query("ALTER TABLE $wpdb->users ADD xt_uid int(30)");
	}
	$q = $wpdb->query("select xt_avatar from $wpdb->users");
	if(!$q){
		$wpdb->query("ALTER TABLE $wpdb->users ADD xt_avatar varchar(100)");
	}
}

function xt_set_login($login){
	global $wpdb;
	if(!function_exists('is_user_logged_in')) require (ABSPATH . WPINC . '/pluggable.php');

		$id = $wpdb->get_var("select ID from {$wpdb->users} where user_login='{$login}'");
		$user = get_user_by( 'id', $id );
		wp_set_current_user($id, $user->user_login);
		wp_set_auth_cookie($id);
		do_action('wp_login', $user->user_login);
}
if(!function_exists('xt_xtAuthLoginFrom')){
	function xt_xtAuthLoginFrom(){
		?>
		<span onclick="window.location.href='?xtauth=true'"   style="display:block;cursor: pointer;height:40px;">
			<img style="width:160px;height:32px;" src="<?php echo plugins_url('login.png',__FILE__);?>" alt="七空幻音账号登录">
		</span>
		<?php

	}
}

function register_xtauth_setting() {
	register_setting( 'update_xtauth', 'xtauth_appid');
	register_setting( 'update_xtauth', 'xtauth_appkey');
	register_setting( 'update_xtauth', 'xtauth_background');
}
add_action( 'admin_init', 'register_xtauth_setting' );
add_action('login_form', 'xt_xtAuthLoginFrom');
function xt_menu() {
	add_menu_page( '七空幻音授权设置', '七空幻音授权设置', 'administrator', 'update_xtauth', 'xt_menu_content',plugins_url( '/xt-icon.ico', __FILE__ ));
}
function xt_menu_content(){ ?>
	<h2>七空幻音授权插件设置</h2>
	<table class="form-table">
	<form method="post" action="options.php">
	<?php settings_fields('update_xtauth');?>
	<tr valign="top">
	<th scope="row"><label for="xtauth_appid">App id:</label></th>
	<td><input type="text" class="regular-text" value="<?php echo get_option('xtauth_appid')?>" id="xtauth_appid" name="xtauth_appid"></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="xtauth_appkey">App Key:</label></th>
	<td><input type="text" class="regular-text" value="<?php echo get_option('xtauth_appkey');?>" id="xtauth_appkey" name="xtauth_appkey"></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="callback">登录回调地址:</label></th>
	<td><input type="text" class="regular-text" readonly value="<?php echo plugins_url('callback.php', __FILE__)?>" id="callback" name="callback"></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="xtauth_background">授权页面背景图片:</label></th>
	<td><input type="text" class="regular-text" value="<?php echo get_option('xtauth_background');?>" id="xtauth_background" name="xtauth_background"></td>
	</tr>
	</table>
	<?php submit_button();?>
	</form>
	<?php
}
add_action('admin_menu','xt_menu');
function xtauth_bind()
{
	?>
	<table class="form-table">
		<tr>
			<th scope="row"><label>七空幻音登录绑定状态</label></th>
			<td>
				<?php if(isBindXtAuth(get_current_user_id())){ ?>
					<a href="<?php echo wp_login_url();?>?xtauth=unbind" style="background-color:#ed2c2c;color:#fff;text-align:center;text-decoration: none;padding:10px 20px;border-radius:3px;">解除绑定</a>
				<?php }else{ ?>

					<a href="<?php echo wp_login_url();?>?xtauth=true" style="background-color:#f5872e;color:#fff;text-align:center;text-decoration: none;padding:10px 20px;border-radius:3px;">开始绑定</a>
				<?php } ?>
		</tr>
	</table>
	<?php
}
	add_action('show_user_profile','xtauth_bind');
