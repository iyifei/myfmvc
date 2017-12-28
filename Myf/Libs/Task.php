<?php
/**
 * 任务基类
 * User: myf
 * Date: 17/3/1
 * Time: 下午3:19
 */

namespace Myf\Libs;


use FastSimpleHTMLDom\Document;

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


    /**
     *
     * @param $url
     * @param string $charset 网页格式,utf8,gbk
     * @return Document
     */
    public function file_get_html($url,$charset='utf8'){
        $htmlContent = $this->curl($url,$charset);
        return Document::str_get_html($htmlContent);
    }

    public function getNeedBetween($kw1, $mark1, $mark2) {
        $kw = $kw1;
        $st = stripos($kw, $mark1);
        $ed = stripos($kw, $mark2);
        if (($st == false || $ed == false) || $st >= $ed)
            return 0;
        $kw = substr($kw, ($st + 1), ($ed - $st - 1));
        return $kw;
    }

    public function curl($url,$charset='utf8'){
        $ch = curl_init();
        $randIp = rand(10,99).".".rand(10,99).".".rand(10,99).".".rand(1,99);
        $header = array(
            'CLIENT-IP:'.$randIp,
            'X-FORWARDED-FOR:'.$randIp,
        );
        $id = rand(1,20000);
        //curl_setopt($ch,CURLOPT_PROXYTYPE,CURLPROXY_SOCKS5);//使用了SOCKS5代理
        //curl_setopt($ch, CURLOPT_PROXY, "121.204.165.159:8118");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.{$id};)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,5);
        $content = curl_exec($ch);
        curl_close($ch);
        if($charset=='gbk'){
            $content =  mb_convert_encoding($content, "UTF-8", "GB2312");
        }
        return $content;
    }

    public function downImg($url,$type){
        $urls = explode('.',$url);
        $suffix = end($urls);
        $uuid = md5($url);
        $fileName=  sprintf("%s/%s/%s.%s",$type,substr($uuid,-6),$uuid,$suffix);
        echo sprintf(">> down %s > ",$url);
        Image::downImage($url,$fileName);
        echo sprintf("%s\n",$fileName);
        return $fileName;
    }

}