<?php
/**
 * 系统入口
 * User: www.minyifei.cn
 * Date: 17/2/22
 * Time: 下午9:07
 */



use Myf\Libs\Log;
use Myf\Libs\Utils;

error_reporting(E_ALL ^ E_NOTICE);
define("APP_DIR",__DIR__);

//系统常量
define('NOW_TIME', $_SERVER['REQUEST_TIME']);
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['ajax']) || !empty($_GET['ajax'])) ? true : false);

Utils::getLogId(true);
Utils::logRequestStart();

register_shutdown_function('shutdown');
//配置smarty
$smt = new \Smarty();
$smt->left_delimiter = "{";
$smt->right_delimiter = "}";
//模板目录，模板命名为方法名驼峰转下划线，再小写
$tmpDir = SYS_PATH."/tpl/";
//模板编译缓存目录
$tmpPath = config('base.smt.tmp_path');
if(empty($tmpPath)){
    $tmpPath = SYS_PATH.'/_smt';
}
$htmlPath = config('base.html.path');
if(empty($htmlPath)){
    $htmlPath = SYS_PATH."/_html";
}
define('HTML_PATH',$htmlPath);
define('CACHE_PATH',SYS_PATH.'_cache');
$cmpDir = $tmpPath."/app/tpl_c";
$smt->setTemplateDir($tmpDir)->setCompileDir($cmpDir);
//目录
$smt->assign('myf_path',getBasePath());
$smt->assign('myf_full_path',getProjectURL());
//配置全局smarty
global $_gblSmarty ;
$_gblSmarty=$smt;

//获取路由解析器
$route = getMvcRoute();
//控制器
$mvcController = $route['c'];
//执行方法
$mvcAction = $route['a'].'Action';
//控制器文件命名雇主为: XxxController
$mvcControllerFileName = $mvcController.'Controller';
//家政控制器文件
$mvcControllerFile = sprintf("%s/controller/Mvc/%s.php",APP_DIR,$mvcControllerFileName);
if(file_exists($mvcControllerFile)){
    try{
        require_once $mvcControllerFile;
        $mvcControllerFileName = sprintf("\Myf\Controller\Mvc\%s",$mvcControllerFileName);
        $myfC = new $mvcControllerFileName();
        //初始化系统方法
        $myfC->_sys_init_action($route);
        //执行前置函数
        $myfC->_before_action();
        //执行用户方法
        $myfC->{$mvcAction}();
        //执行后置函数
        $myfC->_after_action();
    }catch (\Exception $e){
        Log::error(($e->getMessage()));
        $res = [
            'status'=>$e->getCode(),
            'error'=>$e->getMessage()
        ];
        session('returnData',$res);
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($res);
        exit;
    }
}else{
    echo '404';
}