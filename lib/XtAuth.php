<?php
if(!isset($_SESSION))session_start();
class XtAuth{

    private $appid;
    private $appkey;
    private $callback;

    private $request_url = "http://api.acgxt.com/";

    public function __construct($appid,$appkey,$callback){
        $this->appid = $appid;
        $this->appkey = $appkey;
        $this->callback = $callback;
    }

    public function getUrl(){
        $request = $this->request_url;
        $request .= "oauth";
        $request .="?";
        $sign = md5($this->xt_encode(time()));
        $request .="response_type=code&client_id=".$this->appid."&redirect_uri=".urlencode($this->callback)."&sign=".$sign;
        $_SESSION['access_sign'] = $sign;
        return $request;
    }

    public function get_token($code){
        $callback = $this->callback;
        $appkey = $this->appkey;
        $appid = $this->appid;
        $requestUrl = $this->request_url."oauth/accessToken";
        $data = [
            'response_type'=>'token',
            'code'=>$code,
            'client_id'=>$appid,
            'appkey'=>$this->xt_encode($appkey),
            'callback'=>$callback,
            'site'=>$_SERVER['SERVER_NAME']
        ];
        $result = $this->http_request($requestUrl,($data));
        return $result;
    }
    public function isJson($json){
	    if(is_null($json))return false;
	    if(is_array($json))return false;
	    if(is_numeric($json))return false;
	    if(is_bool($json))return false;
	    if(is_array(json_decode($json,true))) return true;return false;
    }
    public function saveAccessToken($token){
	    $_SESSION['access_token'] = $token;
        setcookie("xt_atk",$this->xt_encode($token),time()+86400*7,'/',$_SERVER['SERVER_NAME']);
    }
    public function quit(){
        unset($_SESSION['access_token']);
        setcookie("xt_atk",null,time()-86400*7,'/',$_SERVER['SERVER_NAME']);
    }
    public function getAccessToken(){
        if(isset($_SESSION['access_token']))return base64_encode(base64_decode($_SESSION['access_token']));
        if(isset($_COOKIE['xt_atk']))return base64_encode(base64_decode($this->xt_decode($_COOKIE['xt_atk'])));
        return null;
    }
    public function isLogin(){
        if($this->getAccessToken()==null)return false;return true;
    }
    public function get($path,$params=[]){
	    $u = $this->request_url;
	    $u .= 'interfaces/';
	    $u .= $path."?";
	    $i=0;
	    foreach($params as $k=>$v){
		    $i++;
		    if($i==count($params)){
		        $u .= $k."=".$v;
            }else{
		        $u .= $k."=".$v."&";
	        }
        }
	    return $this->http($u);
    }
    private function http($url){
        $curl=curl_init($url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $outPut=curl_exec($curl);
        return $outPut;
    }
    private function http_request($url,$data = null,$header=[]){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }





    private function xt_encode($text,$k=null){
        $k = $k==null?$this->appkey:$k;
        $key = pack('H*', md5(bin2hex(($k))));
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $text = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,$text, MCRYPT_MODE_CBC, $iv);
        $text = $iv.$text;
        return base64_encode($text);
    }
	private function xt_decode($text,$k=null){
	    $k = $k==null?$this->appkey:$k;
        $key = pack('H*',md5(bin2hex(($k))));
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $text = base64_decode($text);
        $iv_dec = substr($text, 0, $ivSize);
        $text = substr($text, $ivSize);
        $text = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv_dec);
        return $text;
    }


}

?>
