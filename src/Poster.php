<?php

/*
 * 
 * 制作海报
 * 初始稿，仅供参考
 * 
 */

namespace Src;

use Src\IdCreate;

use think\Image;


class Poster extends Image{
    
    //是否需要背景图片
    const IS_BG_OPEN = 0; //生成白色背景图片
    const IS_BG_DOWN = 1; //不生成
    
    //是否在浏览器里显示 默认显示
    const IS_SHOW_DISPLAY = 0; //不显示
    const IS_SHOW_SHOW = 1; //显示

    //生成图片存储地址
    public $file = "";

    public $instance;
    
    public $height; //累积高度
    public $width; //累积宽度
    public $x; //x轴
    public $y; //y轴
    private $msgError;
    
    private $bg_soure; //获取背景文件的信息
    



    private $bg = [ //创建背景要素
        'width' => '',
        'height' => '',
    ]; 
    
    public $config = [
        'imageDefault' => "",
        'is_bg' => 0, // 是否生成白色背景图片 0 生成  2 否  默认生成
        'is_show' => 0, //是否浏览器显示  0 不显示  1 显示
        'src_soure' => "", //需要打水印图片
        'data' => "",  //存储数据
    ];

    public function __construct($width = 500,$height = 750) {
        
        $this->bg['width'] = $width;
        
        $this->bg['height'] = $height;
    }

    public function makeing(array $config)
    {
        $this->config['is_bg'] = isset($config['is_bg']) ? $config['is_bg']:self::IS_BG_OPEN; //默认生成白色背景图片
        $this->config['is_show']  =  isset($config['is_show']) ? $config['is_bg']:self::IS_SHOW_SHOW;
        $this->config['src_soure'] = isset($config['src_soure']) ? $config['src_soure']: "";
        $this->config['data'] = isset($config['data']) ? $config['data'] : [];
//        if ($is_bg == self::IS_BG_OPEN) {
//            $file = $this->background();
//            $this->bg_soure = $this->open($file);
//        } else if ($is_bg == self::IS_BG_DOWN){
//            $this->bg_soure = $imageDefault;
//        } else {
//            $this->msgError = "设置is_bg类型错";
//            return FALSE;
//        }
        $path = dirname(__FILE__) ."/../\\resource\\images";
        $file_name ="\\4857556996592082.jpg";
        $filename = $path . $file_name;
        $this->bg_soure = $this->open($filename);
      
        $info=getimagesize($this->config['src_soure']); //获取图片大小
        $this->y = 0;
        $this->x = 0;
        $this->height = $info[1] + $this->y;
        $this->bg_soure->water($this->config['src_soure'],[$this->x, $this->y], 100); //起始
        $font = $this->font();
      
        $this->x = $config['data']["x"];
        $this->y = $config['data']["y"];
        
        $this->width += $this->x;    //整体调整记录宽度
        $this->height += $this->y; //整体调整记录高度
        foreach ($config['data']['data'] as $key =>$val){
            $split=$this->autoLineSplit(html_entity_decode($val['conetent']),$font , $val['font'], "utf-8", $this->bg['width'] - $this->width); //折行
            
            foreach ($split as $k=>$v){
                
                $this->bg_soure->text($v, $font, $val['font'],"#00000000",[$this->width, $this->height+=$val['y']]);    
                
            }
            
            
        }
        if (isset($config['data']['is_table']) && isset($config['data']['is_table'])){
            
            $this->table();
            
        }
        if ($this->config['is_show']) { //在浏览器里显示图片
            header('Content-type:image/png'); 
           
            imagepng($this->bg_soure->im);
            exit();
        } else {
            $path = $path .'//'. md5($this->config['src_soure']).".jpg";
            imagepng($this->bg_soure->im,$path); //保存图片
        }
        
        
    }
    /**
     * 生成表格
     */
    public function table()
    {
        $font = $this->font();
        //头部
        $header_hight = isset($this->config['data']['header']['height']) ? $this->config['data']['header']['height']:40;
        $color = imagecolorallocate($this->bg_soure->im,125,125,125);
        $this->height += $this->y+20;
        $this->imagelinethick($this->bg_soure->im, $this->width, $this->height,$this->width,$this->height +$header_hight,$color,2);//竖
        $this->imagelinethick($this->bg_soure->im, $this->width, $this->height,$this->bg['width']-$this->width,$this->height,$color,2);//横
        $this->imagelinethick($this->bg_soure->im, $this->bg['width']-$this->width, $this->height,$this->bg['width']-$this->width,$this->height +$header_hight,$color,2);//竖
        $this->imagelinethick($this->bg_soure->im, $this->width, $this->height+$header_hight,$this->bg['width']-$this->width,$this->height+$header_hight,$color,2);//横
        
        $count = count($this->config['data']['header']['fields']);
        
        //每列的宽度
        $gap_widht = ($this->bg['width']-$this->width-$this->width)/$count;

        for ($index = 1; $index <= $count; $index++) {
           
           // $len = (strlen( $this->config['data']['header'][$index-1]) + mb_strlen( $this->config['data']['header'][$index-1], "utf8")) / 2;
            // 计算总占宽
            $dimensions = imagettfbbox(8, 0, $font, $this->config['data']['header']['fields'][$index-1]);
            
            $textWidth = abs($dimensions[4] - $dimensions[0]);
            $textHight = ($header_hight - abs($dimensions[5] - $dimensions[1]))/2;
           
            $gad_kuai = ($gap_widht - $textWidth) /2;
             
            $this->bg_soure->text($this->config['data']['header']['fields'][$index-1], $font, $this->config['data']['header']['font'],"#00000000",[$this->width + ($gap_widht) * $index - $textWidth-$gad_kuai-$this->x/2+3, $this->height + $textHight ]); 
           
            $this->imagelinethick($this->bg_soure->im, $this->width+$gap_widht * $index, $this->height,$this->width+$gap_widht * $index,$this->height +$header_hight,$color,2);//竖
        
             
        }
        //body 中部
        for ($num = 1; $num <= count($this->config['data']['body']['data']); $num++) {
            //标记高度
            $this->height  += $this->config['data']['body']["height"];
            $this->imagelinethick($this->bg_soure->im, $this->width, $this->height,$this->width,$this->height +$header_hight,$color,2);//竖
            $this->imagelinethick($this->bg_soure->im, $this->width, $this->height,$this->bg['width']-$this->width,$this->height,$color,2);//横
            $this->imagelinethick($this->bg_soure->im, $this->bg['width']-$this->width, $this->height,$this->bg['width']-$this->width,$this->height +$header_hight,$color,2);//竖
            $this->imagelinethick($this->bg_soure->im, $this->width, $this->height+$header_hight,$this->bg['width']-$this->width,$this->height+$header_hight,$color,2);//横
            
            $number = count($this->config['data']['body']['data'][$num-1]);
            
            for ($index1 = 1; $index1 <= $number; $index1++) {
               // var_dump($this->config['data']['body']['data'][$index1-1]);
                $dimensions = imagettfbbox(8, 0, $font, $this->config['data']['body']['data'][$num-1][$index1-1]);
                $textWidth = abs($dimensions[4] - $dimensions[0]);
                $textHight = ($header_hight - abs($dimensions[5] - $dimensions[1]))/2;
                 
                $gad_kuai = ($gap_widht - $textWidth) /2;
                
                $this->bg_soure->text($this->config['data']['body']['data'][$num-1][$index1-1], $font, $this->config['data']['header']['font'],"#00000000",[$this->width + ($gap_widht) * $index1 - $textWidth-$gad_kuai-$this->x/2+3, $this->height + $textHight ]); 

                $this->imagelinethick($this->bg_soure->im, $this->width+$gap_widht * $index1, $this->height,$this->width+$gap_widht * $index1,$this->height +$header_hight,$color,2);//竖    

            }
            
         }
    }
    /**
     *  字体
     * @param type $file 设置字体路径
     * @return type 返回路径地址
     */
    public function font ( $file = null ) : string
    {
        $path = dirname(__FILE__) ."/../\\resource\\font\\";
        $font_name = "simsun.ttc";
        return $file ?: $path.$font_name;
    }

    /**
     * 生成白色背景
     */
    public function background()
    {
        $bg = imagecreatetruecolor($this->bg['width'], $this->bg['height']); //创建画布 一幅大小为 x和 y的图像
        $white = imagecolorallocate($bg, 255, 255, 255); //默认白色
        imagefill($bg, 0, 0, $white); //填充颜色
        
        $file_name = IdCreate::createOnlyId();
        $ext = "jpg";
        $path = dirname(__FILE__) ."/../\\resource\\images";
        if(!is_dir($path)){
            return $this->msgError = "保存文件路径不存在";
        }
       
        $this->file = $this->file ?: $path ."\\". $file_name . "." . $ext; 
       
        imagejpeg ($bg, $this->file);//保存图片
        return $this->file;
    }
    
    /**
     * 折行
     * @param array  参数,包括图片和文字
     * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
     * @return [type] [description]
     */

    protected function autoLineSplit ($str, $fontFamily, $fontSize, $charset, $width) {
         $result = [];

         $len = (strlen($str) + mb_strlen($str, $charset)) / 2;

         // 计算总占宽
         $dimensions = imagettfbbox($fontSize, 0, $fontFamily, $str);
         $textWidth = abs($dimensions[4] - $dimensions[0]);

         // 计算每个字符的长度
         $singleW = $textWidth / $len;
         // 计算每行最多容纳多少个字符
         $maxCount = floor($width / $singleW);

         while ($len > $maxCount) {
             // 成功取得一行
             $result[] = mb_strimwidth($str, 0, $maxCount, '', $charset);
             // 移除上一行的字符
             $str = str_replace($result[count($result) - 1], '', $str);
             // 重新计算长度
             $len = (strlen($str) + mb_strlen($str, $charset)) / 2;
         }
         // 最后一行在循环结束时执行
         $result[] = $str;

         return $result;
    } 
    
    //画线
    public function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
    {
        /* 下面两行只在线段直角相交时好使
        imagesetthickness($image, $thick);
        return imageline($image, $x1, $y1, $x2, $y2, $color);
        */
        if ($thick == 1) {
            return imageline($image, $x1, $y1, $x2, $y2, $color);
        }
        $t = $thick / 2 - 0.5;
        if ($x1 == $x2 || $y1 == $y2) {
            return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
        }
        $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
        $a = $t / sqrt(1 + pow($k, 2));
        $points = array(
            round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
            round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
            round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
            round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
        );
        imagefilledpolygon($image, $points, 4, $color);
        return imagepolygon($image, $points, 4, $color);
    }
}
