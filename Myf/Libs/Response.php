<?php
/**
 * 输出文件
 * User: myf
 * Date: 17/2/23
 * Time: 下午5:31
 */

namespace Myf\Libs;


/**
 * Class Response
 * @package KuXue\Cms\Libs
 */
class Response {

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    public function error($message = '', $jumpUrl = '', $ajax = false) {
        if(empty($jumpUrl)){
            $jumpUrl = $_SERVER['HTTP_REFERER'];
        }
        $this->dispatchJump($message, 1, $jumpUrl, $ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    public function success($message = '', $jumpUrl = '', $ajax = false) {
        $this->dispatchJump($message, 0, $jumpUrl, $ajax);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param int $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    public function dispatchJump($message, $status = 0, $jumpUrl = '', $ajax = false) {
        if (true == $ajax || IS_AJAX) {
            $data = is_array($ajax) ? $ajax : array();
            $data['info'] = $message;
            $data['status'] = $status;
            $data['url'] = $jumpUrl;
            $data['state']=($status==0)?'success':"fail";
            $this->ajaxReturn($data);
        } else {
            //页面跳转
            global $_gblSmarty;
            $_gblSmarty->assign('msg',$message);
            $_gblSmarty->assign('url',$jumpUrl);
            $_gblSmarty->assign('time',1);
            if($status==1){
                $_gblSmarty->assign('state','error');
            }
            $_gblSmarty->display('common/msg.html');
            exit;
        }
    }

    /**
     * Ajax方式返回数据到客户端
     * @access public
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    public function ajaxReturn($data, $type = '') {
        if (empty($type)) $type = 'JSON';
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET['callback']) ? $_GET['callback'] : 'callback';
                exit($handler . '(' . json_encode($data) . ');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
        }
    }


    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    public function redirect($url,$delay=0,$msg='') {
        redirect($url,$delay,$msg);
    }


}