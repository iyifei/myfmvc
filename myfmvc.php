<?php
/**
 * 系统入口
 * User: www.minyifei.cn
 * Date: 17/2/22
 * Time: 下午9:07
 */


use Myf\Libs\Log;
use Myf\Libs\RedisSession;
use Myf\Libs\Response;
use Myf\Libs\Utils;


define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
error_reporting(E_ALL ^ E_NOTICE);

if(IS_CLI){
    global  $argv;
    //通过cli命令行访问
    $count = count($argv);
    if($count>=1){
        $ca = $argv[1];
        $caArr = explode("/",$ca);
        $controllerName = $caArr[0];
        $actionName = "main";
        if(isset($caArr[1])){
            $actionName = $caArr[1];
        }
        $args = [];
        if($count>2){
            for($i=2;$i<$count;$i++){
                $args[]=$argv[$i];
            }
        }
        $actionName = $actionName."Action";
        $className = $controllerName."Task";
        $fileName = APP_PATH."/Task/".$className.".php";
        if(file_exists($fileName)){
            include_once($fileName);
            $task = new $className();
            $task->_before_action();
            if(empty($args)){
                $task->$actionName();
            }else{
                $task->$actionName($args);
            }
            $task->_after_action();
        }else{
            echo sprintf("%s not found\n",$fileName);
        }
    }else{
        echo sprintf("argv not match %s\n",json_encode($argv));
    }

}else{
    //系统常量
    define('NOW_TIME', $_SERVER['REQUEST_TIME']);
    define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
    define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
    define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
    define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
    define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
    define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['ajax']) || !empty($_GET['ajax'])) ? true : false);
    register_shutdown_function('shutdown');

    //配置session方式
    $sessionConfig = config('session.redis');
    if(!empty($sessionConfig)){
        $sRedis = \Myf\Libs\Redis::getInstance(null,$sessionConfig);
        $redisSession = new RedisSession($sRedis);
        $redisSession->register();
    }
    //开启session
    session_start();

    Utils::getLogId(true);
    Utils::logRequestStart();
    //配置smarty
    $smt = new \Smarty();
    $smt->left_delimiter = "{";
    $smt->right_delimiter = "}";
    //模板目录，模板命名为方法名驼峰转下划线，再小写
    $tmpDir = APP_PATH . "/View/";
    //模板编译缓存目录
    $tmpPath = config('base.smt.tmp_path');
    if (empty($tmpPath)) {
        $tmpPath = APP_PATH . '/_smt';
    }
    $htmlPath = config('base.html.path');
    if (empty($htmlPath)) {
        $htmlPath = APP_PATH . "/_html";
    }
    define('HTML_PATH', $htmlPath);
    define('CACHE_PATH', APP_PATH . '_cache');
    $cmpDir = $tmpPath . "/app/tpl_c";
    $smt->setTemplateDir($tmpDir)->setCompileDir($cmpDir);
    //目录
    $smt->assign('myf_path', getBasePath());
    $smt->assign('myf_full_path', getProjectURL());
    //配置全局smarty
    global $_gblSmarty;
    $_gblSmarty = $smt;

    //获取路由解析器
    $route = getMvcRoute();
    //控制器
    $mvcController = $route['c'];
    //执行方法
    $mvcAction = $route['a'] . 'Action';
    //控制器文件命名雇主为: XxxController
    $mvcControllerFileName = $mvcController . 'Controller';
    //家政控制器文件
    $mvcControllerFile = sprintf("%s/Controller/%s.php", APP_PATH, $mvcControllerFileName);
    if (file_exists($mvcControllerFile)) {
        try {
            require_once $mvcControllerFile;
            $mvcControllerFileName = sprintf("\Controller\%s", $mvcControllerFileName);
            $myfC = new $mvcControllerFileName();
            //初始化系统方法
            $myfC->_sys_init_action($route);
            //执行前置函数
            $myfC->_before_action();
            //执行用户方法
            $myfC->{$mvcAction}();
            //执行后置函数
            $myfC->_after_action();
        } catch (\Exception $e) {
            Log::error(($e->getMessage()));
            $res = [
                'status' => $e->getCode(), 'error' => $e->getMessage(),
            ];
            session('returnData', $res);
            (new Response())->ajaxReturn($res);
        }
    } else {
        echo '404';
    }
}

