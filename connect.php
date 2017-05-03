<?php
/**
 * @七空幻音
 * @Copyright 2016
 * @WebSite http://www.acgxt.com
 * oAuth2.0授权登录SDK
 */
session_start();//必须开启session
include "config.php";
include "lib/XtAuth.php";
$XtAuth = new XtAuth(XT_APPID,XT_APPKEY,XT_CALLBACK);

if(!$XtAuth->isLogin()){
	//未登录用户请求授权
	header("location:".$XtAuth->getUrl());
}else{
	//已登录用户
	header('location:user.php');
}