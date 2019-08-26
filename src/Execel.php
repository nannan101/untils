<?php

/*
 * 导出execel文件
 */

namespace Src;
set_time_limit(0);


class Execel {

    /**
     * 一键导 出Excel
     * @param array $header       Excel 头部 ["COL1","COL2","COL3",...]
     * @param array $body         和头部长度相等字段查询出的数据就可以直接导出
     * @param int $limt           多少条分页( 导出excel 超出65536)  2003 6万5千行 256列   2007 - 2010 104 行 1万6列 
     * @param null|string $name   文件名，不包含扩展名，为空默认为当前时间
     * @param string|int $version Excel版本 2003|2007
     * @return string
     */
    public static function export($head, $body,$limit = 300, $name = null, $version = '2007')
    {
        try {
            // 输出 Excel 文件头
            $name = empty($name) ? date('YmdHis') : $name;
            
            //phpExcel大数据量情况下内存溢出解决
            $cacheMethod  = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize'=>'16MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            //写入数据
            $objPHPExcel = new \PHPExcel();
            
            $chunk_body = array_chunk($body, $limit);
            
            // Excel body 部分
            foreach ($chunk_body as $key => $value) {
                $objPHPExcel->createSheet();
                $sheetPHPExcel = $objPHPExcel->setActiveSheetIndex($key);
                $char_index = range("A", "Z");
                // Excel 表格头
                foreach ($head as $key1 => $val1) {
                    $sheetPHPExcel->setCellValue("{$char_index[$key1]}1", $val1);
                }
                //Excel 表格内容
                foreach ($value as $k=>$v){
                     $row = $k + 2;
                     $col = 0;
                     foreach ($v as $k1 => $v1) {
                        $sheetPHPExcel->setCellValue("{$char_index[$col]}{$row}", $v1);
                        $col++;
                    }
                }
               
            }
            // 版本差异信息
            $version_opt = [
                '2007' => [
                    'mime'       => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'ext'        => '.xlsx',
                    'write_type' => 'Excel2007',
                ],
                '2003' => ['mime'       => 'application/vnd.ms-excel',
                           'ext'        => '.xls',
                           'write_type' => 'Excel5',
                ],
                'pdf'  => ['mime'       => 'application/pdf',
                           'ext'        => '.pdf',
                           'write_type' => 'PDF',
                ],
                'ods'  => ['mime'       => 'application/vnd.oasis.opendocument.spreadsheet',
                           'ext'        => '.ods',
                           'write_type' => 'OpenDocument',
                ],
            ];

            header('Content-Type: ' . $version_opt[$version]['mime']);
            header('Content-Disposition: attachment;filename="' . $name . $version_opt[$version]['ext'] . '"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, $version_opt[$version]['write_type']);
            $objWriter->save('php://output');
            exit();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
}
