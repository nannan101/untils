<?php

include 'vendor/autoload.php';

use Src\Token;
use Src\IdCreate;
use Src\Poster;
use Src\Execel;
use Src\MhtFileMaker;
use Src\Cutpage;

class Run {
   
    public function jwtEndcode()
    {
        //示例
        return Token::getInstance()->encode([
            'id'=>1,
            'name'=> '张三',
        ]);
        
    }
    
    public function jwtDecode()
    {
        //示例
        $jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ3d3cuemhvbmdjaG91Lm5ldCIsImF1ZCI6Ind3dy56aG9uZ2Nob3UubmV0IiwiaWF0IjoxNTY1OTIwNDkxLCJuYmYiOjE1NjU5MjA0OTEsImRhdGEiOnsiaWQiOjEsIm5hbWUiOiJcdTVmMjBcdTRlMDkifX0.jbBkjf4AMBwjQshkxn52lS-7lslLvSTAf9ccfohk2fM";
      
        if (($obj = Token::getInstance()->decode($jwt)) == FALSE) {
           
            return Token::getInstance()->getError();
            
        }
        return $obj->data;
        
    }
    
    /**
     * 雪花算法 生成唯一的ID
     */
    public function idCreate()
    {
        return IdCreate::createOnlyId();
    }
    
    public function poster()
    {
        $poster = new Poster();
        $config = [
          'src_soure' => 'F:\\ddd.png',
          'data' => [
               //整体调整
               'x' => 20, //初始行间隔
               'y' => 10, //间隔
               'line' => 5, //每行间隔,
               'data' => [
                   //局部调整
                    [
                       'x' =>10,  //x轴
                       'y' => 10, //y轴
                       'font'=>12, //字体大小
                       'conetent' => '标题:地灵曲',
                    ],
                    [
                       'x' =>10,  //x轴
                       'y' => 30, //y轴
                       'font'=>12, //字体大小
                       'conetent' => '地址:西湖区文三路151',
                    ],
                    [
                       'x' =>10,  //x轴
                       'y' => 30, //y轴
                       'font'=>12, //字体大小
                       'conetent' => '介绍：地灵曲是一款非常火爆的盗墓类游戏，画面刺激，辣眼睛，二十四小时可在线solo，地灵曲根据小说，小说作者一笑九幽，默默多大的 大神制作，150人团队耗时三年打造，即将上映',
                    ],
                ],
                'is_table'=> 1,
                'header'=>[
                    'height'=> 40,
                    'font' => 12,
                    'fields' =>['参赛证号','姓 名','性 别','年 级']
                ],
                'body' => [
                    'font' => 12,
                   'height'=> 40,
                   'data' => [
                       ['xl100034','张三','女','二年级'],
                       ['g1100034','张三','女','三年级'],
                       ['g1100034','王二麻子','女','三年级']
                   ] 
                ]
                
          ],
          
        ];
        $poster->makeing($config);
    }
    public function execel()
    {
        $head = ['编号','姓名','性别','年级'];
        $body = [
            ['order_on'=>'xl100034','name'=>'张三','sex'=>'女','greaner' => '二年级'],
            ['order_on'=>'g1100034','name'=>'张三','sex'=>'女','greaner' => '三年级'],
            ['order_on'=>'g1100034','name'=>'王二麻子','sex'=>'女','greaner' => '三年级'],
        ];
        Execel::export($head, $body, 2);
        
    }
    /**
     * html转word
     * @throws Exception
     */
    public function html2word()
    {
        // 本地下载
        MhtFileMaker::getInstance()
            ->addFile('resource/worker/tpl.html')
            ->eraseLink()
            ->fetchImg('http://utils.test')
            ->makeFile('resource/a.doc');

        // 浏览器下载
        MhtFileMaker::getInstance()
            ->addFile('resource/worker/tpl.html')
            ->fetchImg('http://utils.test')
            ->download();
    }
    /**
     * 长文章分页
     */
    public function cutpage()
    {
        $pagestr = file_get_contents("resource/text.txt");
        
        $cp = new Cutpage($pagestr,100);
        
        $ipage = isset($_GET["ipage"])? intval($_GET["ipage"]):1;  
        $cut_str = $cp->cut_str();
       
        $result = [
            'total' => $cp->get_sum_page(),
            'content' => $cut_str[$ipage -1], 
            'pages' => $cp->pagenav(),
        ];
        return $result;
    }
}

$test = new Run();
var_dump($test->cutpage());
