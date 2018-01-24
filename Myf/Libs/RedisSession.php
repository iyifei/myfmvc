<?php
/**
 * 跨服务器session处理
 * User: myf
 * Date: 2018/1/24
 * Time: 10:43
 */

namespace Myf\Libs;


class RedisSession implements \SessionHandlerInterface
{
    private $redis = null;
    private $maxLifeTime;
    private $keyPrefix;

    public function __construct($redis,$options=[]) {
        //session的前缀
        $keyPrefix='_myf_session:';
        if(isset($options['keyPrefix'])){
            $keyPrefix = $options['keyPrefix'];
        }
        //session过期时间
        if(isset($options['gc_maxlifetime'])){
            $this->maxLifeTime = intval($options['gc_maxlifetime']);
        }else{
            $this->maxLifeTime = ini_get('session.gc_maxlifetime');
        }
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
        return true;
    }

    public function register(){
        if (PHP_VERSION_ID >= 50400) {
            session_set_save_handler($this, true);
        } else {
            session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'gc')
            );
        }
    }

    public function open($savePath,$sessionName){
        return true;
    }

    public function close(){
        return true;
    }

    public function read($key){
        $skey = $this->keyPrefix.$key;
        $data = $this->redis->get($skey);
        if(!empty($data)){
            $this->redis->expire($skey,$this->maxLifeTime);
            return $data;
        }else{
            return false;
        }
    }

    public function write($key,$val){
        $skey = $this->keyPrefix.$key;
        $this->redis->set($skey,$val);
        $this->redis->expire($skey,$this->maxLifeTime);
        return true;
    }

    public function destroy($key){
        $skey = $this->keyPrefix.$key;
        $this->redis->del($skey);
        return true;
    }

    public function gc($maxlifetime) {
        return true;
    }

}