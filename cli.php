<?php
/**
 * 命令
 * User: myf
 * Date: 17/3/1
 * Time: 下午3:16
 */
$count = count($argv);
if($count>=2){
    $controllerName = $argv[1];
    $actionName = "main";
    if(isset($argv[2])){
        $actionName = $argv[2];
    }
    $args = [];
    if($count>3){
        for($i=3;$i<$count;$i++){
            $args[]=$argv[$i];
        }
    }
    $actionName = $actionName."Action";
    $className = $controllerName."Task";
    $fileName = SYS_PATH."/task/".$className.".php";
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