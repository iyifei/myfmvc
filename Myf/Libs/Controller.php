<?php
/**
 * MVC基础Controller
 * User: myf
 * Date: 17/2/23
 * Time: 上午10:31
 */

namespace Myf\Libs;


class Controller {

    private $smt;
    private $_actionName;
    private $_controllerName;
    private $_response;

    function __construct() {
        $this->_response = new Response();
    }


    function _sys_init_action($route) {
        global $_gblSmarty;
        $this->smt = $_gblSmarty;
        //$this->smt->default_modifiers = array('$' => 'escape:"html"');
        $this->_controllerName = $route['_c'];
        $this->_actionName = $route['_a'];
        $this->assign('_action', $this->_actionName);
        $this->assign('_controller', $this->_controllerName);
    }


    /**
     * 重置smarty模板目录
     * @param $dir
     */
    public function setTemplateDir($dir) {
        $this->smt->setTemplateDir($dir);
    }

    /**
     * 添加插件目录
     * @param type $dir
     */
    public function addPluginsDir($dir) {
        $this->smt->addPluginsDir($dir);
    }

    /**
     * 重置smartyDelimiter
     * @param type $left
     * @param type $right
     */
    public function setDelimiter($left, $right) {
        $this->smt->left_delimiter = $left;
        $this->smt->right_delimiter = $right;
    }


    /**
     * action前执行的全局方法，可继承并重构
     */
    public function _before_action() {

    }

    /**
     * action后执行的全局方法,可继承并重构
     */
    public function _after_action() {

    }

    /**
     * smarty 设置模板变量
     * @param String $name
     * @param $value
     */
    public function assign($name, $value) {
        $this->smt->assign($name, $value);
    }


    /**
     * 输出json
     * @param $data
     */
    public function echoJson($data){
        session('returnData',$data);
        $this->_response->ajaxReturn($data,'JSON');
    }

    /**
     * 输出正常json结果
     * @param $data
     */
    public function echoSuccess($data){
        $res = [
            'status'=>MyfException::SUCCESS_CODE,
            'data'=>$data,
        ];
        $this->echoJson($res);
    }

    /**
     * 获取模板解析后的内容
     * @param String $tplName 模板名称
     * @return String 模板解析后的内容
     */
    public function fetch($tplName = null, $compress = true) {
        if (empty($tplName)) {
            $tplName = $this->_controllerName . '/' . $this->_actionName . '.html';
        }
        $content = $this->smt->fetch($tplName);
        if($compress){
            $content = compress_html($content);
        }
        return $content;
    }

    /**
     * 显示模板解析后的内容
     * @param String $tplName 模板名称
     * @param bool $compress 是否压缩html代码
     */
    public function display($tplName = null, $compress = false) {
        if (empty($tplName)) {
            $tplName = $this->_controllerName . '/' . $this->_actionName . '.html';
        }
        if ($compress) {
            $content = $this->fetch($tplName,$compress);
            echo $content;
        } else {
            $this->smt->display($tplName);
        }
    }

    /**
     * 错误跳转
     * @param $msg
     * @param $url
     */
    public function error($msg, $url = null) {
        $this->_response->error($msg, $url);
    }

    /**
     * 成功跳转
     * @param $msg
     * @param $url
     */
    public function success($msg, $url = null) {
        $this->_response->success($msg, $url);
    }

    /**
     * 魔术方法
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments) {
        header("http/1.1 404 not found");
        header("status: 404 not found");
        echo sprintf("[%s] error url 404", $name);
    }


    public function makeUrl($controller, $action, $param = '') {
        $url = sprintf('%s/index.php?c=%s&a=%s&%s', getBasePath(), $controller, $action, $param);
        return $url;
    }

}