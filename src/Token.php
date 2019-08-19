<?php

namespace Src;

use Firebase\JWT\JWT;

class Token {
  
    public $key; //secret key
    
    public $alg; //加密方式
    
    private static $instance;
    
    private $errorMsg;

    public $config = [
        "iss" => "", //签发者 可选
        "aud" => "",//接收该JWT的一方，可选
        "iat" => "",//签发时间 时间戳
        "nbf" => "", //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
        "exp" => "",//过期时间,这里设置2个小时,时间戳 7200
        "data" => "", //自定义信息，不要定义敏感信息
    ];
    
    public static function getInstance ($key = "jwt" ,$alg = "HS256")
    {
         if (is_null(self::$instance)) {
             self::$instance = new self($key,$alg);
         }
         return self::$instance;
    }
    
    private function __construct ($key, $alg) {
        $this->key = $key;
        $this->alg = $alg;
    }
    
    public function encode ($data, $exp = null, $nbf = null, $iat = null ,$iss = null , $aud = null) { 
       $now =  time() ;
       if (is_null($exp)) {
           unset( $this->config['exp'] );
       }else{
           $this->config['exp'] = $now + $exp;
       }
       $this->config['iss'] = $iss ?: "www.zhongchou.net";
       $this->config['aud'] = $aud ?: "www.zhongchou.net";
       $this->config['iat'] = $iat ?: $now;
       $this->config['nbf'] = $nbf ?: $now;
       $this->config['data'] = $data;
        
        return JWT::encode($this->config, $this->key, $this->alg);
    }
    
    public function decode($jwt)
    {
        try {
            JWT::$leeway = 60;  //检查nbf，iat或到期时间时，提供额外的余地时间 考虑时钟偏差
            $decoded = JWT::decode($jwt, $this->key, [$this->alg]);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            // 签名不正确
            $this->errorMsg = 'token签名不正确';
            return false;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            // 签名在某个时间点之后才能用
            $this->errorMsg = 'token还未生效';
            return false;
        } catch (\Firebase\JWT\ExpiredException $e) {
            // token过期
            $this->errorMsg = 'token过期';
            return false;
        } catch (\Exception $e) {
            // 其他错误
            $this->errorMsg = 'token不正确';
            return false;
        }
        return $decoded;
    }
    
    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->errorMsg;
    }

    protected function __clone()
    {

    }

    protected function __wakeup()
    {

    }
}
