<?php
namespace Src;
use Hashids\Hashids;


class HashidsHash
{
    /**
     * 设置加密长度
     * 默认长度=10
     * @var int 
     */
    static $length = 10;
    /**
     * 加盐值
     * 默认"mall"
     * @var type 
     */
    static $salt   = "mall";
    /**
     * 加密
     * @param type $data
     * @param id $selectField  加密的字段默认值为id
     * @return type
     */
    public static function hashids_encode($data,$selectField = "id")
    {
        
        $hashids = new Hashids(self::$salt , self::$length); //加密  密码长度
        
        //判读 int
        if (!is_array($data) && $data>0) {
            return $hashids->encode($data);
        }
        if (!is_array($data)) {
            return $data;
        }
        // 判读是否是一维数组
        if(count($data) == count($data,1) && $hash = $hashids->encode($data[$selectField])) {
            $data[$selectField] = $hash;
            return $data;
        }
        //二维数组有效加密 多维没有有效加密
        $result = array_map(function ($value) use ($selectField,$hashids) {
            
            if (isset($value[$selectField]) && $hash = $hashids->encode($value[$selectField])) {
                $value[$selectField] = $hash;
            }
            
            return $value;
        }, $data);
        
        return $result;
    }
    /**
     * 解密
     * @param type $string 加密的字符串
     */
    public static function hashids_decode($string) 
    {
       $hashids = new Hashids(self::$salt , self::$length); //加密  密码长度
        if ($result = $hashids->decode($string)){
            return $result[0];
        }
        return $string; 
    }
    
}
