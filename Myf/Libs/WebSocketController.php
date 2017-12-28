<?php
/**
 * webSocket基类
 * User: myf
 * Date: 2017/11/1
 * Time: 18:44
 */

namespace Myf\Libs;


class WebSocketController extends SwooleController
{

    private $id;

    /**
     * 初始化WebSocketController
     * @param object $ws WebSocket句柄对象
     * @param String $id 请求id
     */
    public function _sys_init_action($ws,$id){
        $this->id = $id;
        $this->_init($ws);
    }

    /**
     * 正常发送消息
     * @param $fd
     * @param $id
     * @param $data
     * @return bool 发送成功或失败
     */
    public function success($fd,$data){
        $res = [
            'id'=>$this->id,
            'data'=>$data,
            'status'=>0,
        ];
        return $this->push($fd,json_encode($res));
    }

    /**
     * 失败发送消息
     * @param $fd
     * @param int $code 错误码
     * @param string $error
     * @return bool 发送成功或失败
     */
    public function error($fd,$code=1,$error=''){
        $res = [
            'id'=>$this->id,
            'error'=>$error,
            'status'=>$code,
        ];
        return $this->push($fd,json_encode($res));
    }

}