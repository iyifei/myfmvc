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
//开启session
session_start();
//读取配置文件
$iniFiles = @dir_files(SYS_PATH . '/config');
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
    $firstName = current($fileNames);
    $cs[$firstName] = include $file;
    $_gblConfig = array_merge($_gblConfig,$cs);
}
