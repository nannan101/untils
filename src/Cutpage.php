<?php  

namespace Src;
/*  
*  长文章分页类     
*/  
class Cutpage{    
    private $pagestr;       //被切分的内容    
    private $pagearr;       //被切分文字的数组格式    
    private $sum_word;      //总字数(UTF-8格式的中文字符也包括)    
    private $sum_page;      //总页数    
    private $page_word;     //一页多少字    
    private $cut_tag;       //自动分页符    
    private $cut_custom;    //手动分页符    
    private $ipage;         //当前切分的页数，第几页    
    private $url;    

    function __construct($pagestr,$page_word=1000){    
        $this->page_word = $page_word;    
        $this->cut_tag = array("</table>", "</div>", "</p>", "<br/>", "”。", "。", ".", "！","……", "？", ",","，");    
        $this->cut_custom = "{nextpage}";    
        $tmp_page = isset($_GET["ipage"]) ? trim($_GET["ipage"]) : 1;
      
        $this->ipage = $tmp_page>1?$tmp_page:1; 
        $this->pagestr = $pagestr;
    }    
    //统计总字数    
    function get_page_word(){    
        $this->sum_word = $this->strlen_utf8($this->pagestr);    
        return $this->sum_word;    
    }
    //统计中页数 
    function get_sum_page()
    {
        return $this->sum_page;
    }
    /*  统计UTF-8编码的字符长度  
     *  一个中文，一个英文都为一个字  
     */  
    function strlen_utf8($str){    
       $i = 0;    
       $count = 0;    
       $len = strlen ($str);    
       while ($i < $len){    
           $chr = ord ($str[$i]);    
           $count++;    
           $i++;    
           if ($i >= $len)    
               break;    
           if ($chr & 0x80){    
               $chr <<= 1;    
               while ($chr & 0x80) {    
                   $i++;    
                   $chr <<= 1;    
               }    
           }    
       }    
       return $count;    
    }    
    //设置自动分页符号    
    function set_cut_tag($tag_arr=array()){    
        $this->cut_tag = $tag_arr;    
    }    
    //设置手动分页符    
    function set_cut_custom($cut_str){    
        $this->cut_custom = $cut_str;    
    }    
    function show_cpage($ipage=0){    
        $this->cut_str();    
        $ipage = $ipage ? $ipage:$this->ipage;    
        return $this->pagearr[$ipage];    
    }    
    function cut_str(){    
        $str_len_word = mb_strlen($this->pagestr);     //获取使用strlen得到的字符总数    
        $i = 0;  
        
        if ($str_len_word<=$this->page_word){   //如果总字数小于一页显示字数    
            $page_arr[$i] = $this->pagestr;    
        }else{    
            if (strpos($this->pagestr, $this->cut_custom)){    
                $page_arr = explode($this->cut_custom, $this->pagestr);    
            }else{   
              
                $str_first = mb_substr($this->pagestr, 0, $this->page_word);   //0-page_word个文字    cutStr为func.global中的函数    
                foreach ($this->cut_tag as $v){    
                    $cut_start = strrpos($str_first, $v);       //逆向查找第一个分页符的位置    
                   
                    if ($cut_start){    
                        $page_arr[$i++] = substr($this->pagestr, 0, $cut_start).$v;    
                        $cut_start = $cut_start + strlen($v);    
                        break;    
                    }    
                } 
               
                if (($cut_start+$this->page_word)>=$str_len_word){  //如果超过总字数    
                    $page_arr[$i++] = substr($this->pagestr, $cut_start, $this->page_word);    
                }else{ 
                   
                    while (($cut_start+$this->page_word)<$str_len_word){    
                          
                            $str_tmp = mb_substr($this->pagestr, $cut_start, $this->page_word);        //取第cut_start个字后的page_word个字符    
                          
                           // $cut_tmp = strrpos($str_tmp, $v);       //找出从第cut_start个字之后，page_word个字之间，逆向查找第一个分页符的位置    
                          
                           $cut_tmp = $cut_start;
                            
                           // if ($cut_tmp){    
                                $page_arr[$i++] = mb_substr($str_tmp, 0, $cut_start);    
                                $cut_start = $cut_start + $cut_tmp;    
                              
                           // }    
                             
                    }  
                  
           
                    if (($cut_start+$this->page_word)>$str_len_word){    
                        $page_arr[$i++] = mb_substr($this->pagestr, $cut_start, $this->page_word);    
                    }    
                }    
            }    
        }    
        $this->sum_page = count($page_arr);     //总页数    
        $this->pagearr = $page_arr;  
        return $page_arr;
    }    
    //显示上一条，下一条    
    function pagenav(){    
        $this->set_url();    
        $str = '';

                    //$str .= $this->ipage.'/'.$this->sum_page;

                    for($i=1;$i<=$this->sum_page;$i++){
                            if($i==$this->ipage) {
                                    $str.= "<a href='#' class='cur'>".$i."</a> ";
                            }else{
                                    $str.= "<a href='?ipage=".$i."'>".$i."</a> ";
                            }
                    }


        return $str;    
    }    
            function show_prv_next2(){    
        $this->set_url();    
        $str = '';



        if ($this->sum_page>1 and $this->ipage>1){    
            $str.= "<a href='".$this->url.($this->ipage-1)."'>上一页</a> ";    
        }   
                    if ($this->sum_page>1 and $this->ipage<$this->sum_page){    
            $str .= "<a href='".$this->url.($this->ipage+1)."'>下一页</a>";    
        }   			
        return $str;    
    }    
    function show_page_select(){    
        if ($this->sum_page>1){    
            $str = "   <select onchange='location.href=this.options[this.selectedIndex].value'>";    
            for ($i=1; $i<=$this->sum_page; $i++){    
                $str.= "<option value='".$this->url.$i."' ".(($this->ipage)==$i ? " selected='selected'":"").">第".$i."页</option>";    
            }    
            $str.= "</select>";    
        }    
        return $str;    
    }    
    function show_page_select_wap(){    
        if ($this->sum_page>1){    
            $str = "<select ivalue='".($this->ipage-1)."'>";    
            for ($i=1; $i<=$this->sum_page; $i++){    
                $str.= "<option onpick='".$this->url.$i."'>第".$i."节</option>";    
            }    
            $str.= "</select>";    
        }    
        return $str;    
    }    
    function set_url(){    
        parse_str($_SERVER["QUERY_STRING"], $arr_url);    
        unset($arr_url["ipage"]);    
        if (empty($arr_url)){    
            $str = "ipage=";    
        }else{    
            $str = http_build_query($arr_url)."&ipage=";    
        }    
        $this->url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]."?".$str;    
    }    
}    
?>