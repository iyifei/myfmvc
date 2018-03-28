<?php
/**
 * 核心类库
 * User: minyifei.cn
 * Date: 17/2/22
 * Time: 下午8:10
 */
//设置时区
date_default_timezone_set('PRC');
//统一编码为utf8
mb_internal_encoding('UTF-8');
//读取配置文件
$iniFiles = @dir_files(COMMON_PATH . '/Config');
$iniOpFiles = @dir_files(OP_CONF_DIR);
$iniFiles = array_merge($iniFiles,$iniOpFiles);
global $_gblConfig;
foreach ($iniFiles as $iniFile) {
    if(!isset($_gblConfig)){
        $_gblConfig=[];
    }
    $file = $iniFile['file'];
    $fileArr = explode("/",$file);
    $fileName = end($fileArr);
    $fileNames = explode(".",$fileName);
    $c = count($fileNames);
    $cs = [];
    if($fileNames[$c-2]=='config'){
        unset($fileNames[$c-1]);
        unset($fileNames[$c-2]);
        $data = include $file;
        switch ($c){
            case 3:
                $cs[$fileNames[0]]=$data;
                break;
            case 4:
                $cs[$fileNames[0]][$fileNames[1]]=$data;
                break;
            case 5:
                $cs[$fileNames[0]][$fileNames[1]][$fileNames[2]]=$data;
                break;
        }
        $_gblConfig = array_merge_recursive($_gblConfig,$cs);
    }
}
spl_autoload_register("loader");