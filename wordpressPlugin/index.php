<?php
/*
Plugin Name: XtAuth
Plugin URI: https://github.com/balrogsxt/xtauth/
Description: 七空幻音oauth2.0授权登录插件,PHP版本请在5.4及以上-7.0及以下
Version: 1.0
Author: 幻音丶小涛
Author URI: http://www.acgxt.com
*/
if(isset($_SESSION))session_start();
include_once('function.php');

if(isset($_GET['xtauth'])){
	$xtauthStatu = $_GET['xtauth'];
	if($xtauthStatu=='unbind'){
		if(!is_user_logged_in()){
			sendErrorHtml('请先登录!','请先登录');
		}else{
			$id = get_current_user_id();
			if(!isBindXtAuth($id)){
				sendErrorHtml('你还没有进行绑定呢!','你还没有进行绑定呢!',admin_url('profile.php'),'返回个人资料');
			}
			global $wpdb;
			$r = $wpdb->query("UPDATE `{$wpdb->users}` SET xt_uid = '0',xt_avatar='' WHERE ID = '$id'");
			if($r){
				sendErrorHtml('解除绑定成功!','解除绑定成功',admin_url('profile.php'),'返回个人资料');
			}else{
				sendErrorHtml('解除绑定发生错误,请稍后再试!','解除绑定发生错误,请稍后再试!',admin_url('profile.php'),'返回个人资料');
			}
		}
	}else{
		$conf = xt_getAuthConfig();
		$xtauth = new XtAuth($conf['appid'],$conf['appkey'],$conf['callback']);
		header("location:".$xtauth->getUrl());
	}
}
