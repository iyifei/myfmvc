<?php
/**
 * 基础类
 * User: myf
 * Date: 17/3/2
 * Time: 下午5:24
 */

namespace Myf\Libs;


class BaseController extends Controller{

    public function _before_action(){
        $user = $this->getCurrentUser();
        if(empty($user)){
            header("Location:" . getBaseURL() . "/auth/login");
            exit;
        }
        $displayName = $user['name'];
        if(empty($user['name'])){
            $displayName = '**'.substr($user['mobile'],-4);
        }
        $user['displayName'] = $displayName;
        $this->assign('currentUser',$user);
    }


    /**
     * 获取当前登录用户信息
     */
    public function getCurrentUser(){
        $user = session('CurrentUser');
        return $user;
    }

    /**
     * 获取当前登录用户信息的id
     * @return mixed
     */
    public function getCurrentUserId(){
        $user =$this->getCurrentUser();
        return $user['id'];
    }


    /**
     * https请求
     * @param $url
     * @return mixed
     */
    public function curlHttps($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        return $res;
    }


}