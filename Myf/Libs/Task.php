<?php
/**
 * 任务基类
 * User: myf
 * Date: 17/3/1
 * Time: 下午3:19
 */

namespace Myf\Libs;

class Task {

    //前置函数
    public function _before_action(){

    }

    //后置函数
    public function _after_action(){

    }

    public function mainAction(){
        echo "hello myfmvc task !";
    }
}