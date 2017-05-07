# xtauth
七空幻音oauth2.0授权登录sdk for php
* config.php配置appid与appkey和回调地址
* 接口地址: http://api.acgxt.com

## 获取用户数据
接口地址:http://api.acgxt.com/interfaces/user
参数:access_token、scope
请求方法:GET
参数说明:
* access_token:授权登录成功后获取的token
* scope:用户请求的资料,可选为(id,user,name,sex,level,exp,coin,avatar,ban,site,email,group,sexid)
* 返回类型:json
### 案例(user.php)
    $data = $XtAuth->get('user',[
	   'access_token'=>$access_token,
	    'scope'=>'id,user,name,sex,level,exp,coin,avatar,ban,site,email,group,sexid,content'
    ]);
    $data = json_decode($data,true);
    $data = $data['data'];
    var_dump($data);
## 其他方法XtAuth类
    public function quit()退出当前授权用户
    public function saveAccessToken($token)保存access_token储存7天
    public function getAccessToken()获取access_token
    public function get($path,$params=[]) GET请求接口 $path:接口名称 $params接口参数
## 错误代码
* 100 response_type值不正确
* 101 code值不正确
* 102 client_id值不正确
* 103 appkey值不正确
* 104 code值不存在
* 105 appid不正确
* 106 令牌过期
* 107 令牌不正确
* 108 不存在的client_id
* 109 appkey不正确
* 110 源站地址不正确
* 111 授权处理失败

* 201 access_token信息错误
* 202 access_token不存在
* 203 access_token已过期(7天内有效)
* 204 第三方授权登录已关闭
* 205 第三方请求来源地址错误


# xtauth for wordpress
## 插件使用方法
* 1.下载或克隆到wp-content/plugins/目录下
* 2.进入后台开启XtAuth
* 3.wordpress菜单中配置Appid与Appkey
* 4.进入wp-login.php进行测试
* 5.完成
