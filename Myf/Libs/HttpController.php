<?php
/**
 * HttpServer基础Controller
 * User: myf
 * Date: 17/2/23
 * Time: 上午10:31
 */

namespace Myf\Libs;


class HttpController extends SwooleController {

    private $request;
    private $response;

    function _sys_init_action($ws,$request,$response) {
        $this->request = $request;
        $this->response = $response;
        $this->_init($ws);
    }


    public function getRequest(){
        return $this->request;
    }

    public function get($key,$default=null){
        $req = $this->request->get;
        if($req[$key]){
            $default = $req[$key];
        }
        return $default;
    }

    public function post($key,$default=null){
        $req = $this->request->post;
        if($req[$key]){
            $default = $req[$key];
        }
        return $default;
    }


    public function getResponse(){
        return $this->response;
    }

    /**
     * 输出内容
     * @param $content
     * @param int $status
     */
    public function response($content,$status=200){
        $this->response->status($status);
        $this->response->end($content);
    }


}
