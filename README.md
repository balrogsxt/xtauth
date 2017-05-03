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
	    'scope'=>'id,user,name,sex,level,exp,coin,avatar,ban,site,email,group,sexid'
    ]);
    $data = json_decode($data,true);
    $data = $data['data'];
    var_dump($data);
## 其他方法XtAuth类
    public function quit()退出当前授权用户
    public function saveAccessToken($token)保存access_token储存7天
    public function getAccessToken()获取access_token
    public function get($path,$params=[]) GET请求接口 $path:接口名称 $params接口参数
