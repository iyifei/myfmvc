<?php
/**
 * 工具类
 * User: myf
 * Date: 17/2/23
 * Time: 上午6:55
 */

namespace Myf\Libs;


class Utils {

    /**
     * 读取一个请求唯一id
     * @return string
     */
    public static function getLogId($new = false) {
        $logId = session('log_id');
        if (empty($logId) || $new) {
            $randStr = uniqid(mt_rand(), true) . self::getMillisecond();
            $logId = md5($randStr);
            session('log_id', $logId);
        }
        return $logId;
    }

    /**
     * 读取当前毫秒数
     */
    static function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 多维数组排序
     * @param $multi_array
     * @param $sort_key
     * @param int $sort
     * @return array|bool
     */
    public static function multiArraySort($multi_array, $sort_key, $sort = SORT_ASC) {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }


    /**
     * 请求入口记录日志函数
     */
    static function logRequestStart() {
        session('_start_time', Utils::getMillisecond());
        $req = [
            'REQUEST'=>$_REQUEST,
            'HEADER'=>getAllHeaders(),
            'METHOD'=>$_SERVER['REQUEST_METHOD'],
            'SCHEME'=>$_SERVER['REQUEST_SCHEME'],
            'IP'=>self::getClientIP(),
        ];
        $msg =  'REQUEST '.jsonCNEncode($req);
        Log::http($msg);
    }

    /**
     * 请求出口记录日志函数
     * @param $response
     */
    static function logResponse($response = '') {
        $ct = Utils::getMillisecond() - session('_start_time');
        $res = [
            'CT'=>$ct.'ms',
            'RESPONSE'=>$response,
        ];
        $msg = 'RESPONSE '.jsonCNEncode($res);
        Log::http($msg);
    }

    /**
     * 获取两个标签之间的内容
     * @param $kw1
     * @param $mark1
     * @param $mark2
     * @return int|string
     */
    public static function getNeedBetween($kw1, $mark1, $mark2) {
        $kw = $kw1;
        $st = stripos($kw, $mark1);
        $ed = stripos($kw, $mark2);
        if (($st == false || $ed == false) || $st >= $ed)
            return 0;
        $kw = substr($kw, ($st + 1), ($ed - $st - 1));
        return $kw;
    }

    /**
     * curl请求获取内容
     * @param $url
     * @param string $charset
     * @return mixed|string
     */
    public static function curl($url, $charset = 'utf8') {
        $ch = curl_init();
        $randIp = rand(10, 99) . "." . rand(10, 99) . "." . rand(10, 99) . "." . rand(1, 99);
        $header = array(
            'CLIENT-IP:' . $randIp,
            'X-FORWARDED-FOR:' . $randIp,
        );
        $id = rand(1, 20000);
        //curl_setopt($ch,CURLOPT_PROXYTYPE,CURLPROXY_SOCKS5);//使用了SOCKS5代理
        //curl_setopt($ch, CURLOPT_PROXY, "121.204.165.159:8118");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.{$id};)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $content = curl_exec($ch);
        curl_close($ch);
        if ($charset == 'gbk') {
            $content = mb_convert_encoding($content, "UTF-8", "GB2312");
        }
        return $content;
    }

   static function isMobileBrowser(){
        // returns true if one of the specified mobile browsers is detected
        // 如果监测到是指定的浏览器之一则返回true
        $regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";

        $regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";

        $regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";

        $regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";

        $regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";

        $regex_match.=")/i";

        // preg_match()方法功能为匹配字符，既第二个参数所含字符是否包含第一个参数所含字符，包含则返回1既true
        return preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
    }


    /**
     * 获取随机字符串
     * @return string
     */
    public static function getUuid() {
        $randStr = uniqid(mt_rand(), true) . self::getMillisecond() . mt_rand(1, 90000000);
        $uuid = md5($randStr);
        return $uuid;
    }


    /**
     * 循环创建文件夹
     * @param $dir
     * @return bool
     */
    public static function createFolders($dir) {
        return is_dir($dir) or (self::createFolders(dirname($dir)) and mkdir($dir, 0777));
    }

    /**
     * JSON处理数据,专供日志输出使用，请误用于其他
     * @param $array
     * @return string
     */
    public static function jsonEncode($array) {
        $json = json_encode($array, JSON_UNESCAPED_UNICODE);
        $json = str_replace('\"', '"', $json);
        $json = str_replace('\\\\\\', '', $json);
        return $json;
    }


    /**
     * 随机字符串
     * @param int $length 长度
     * @return null|string
     */
    public static function getRandString($length) {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }

    /**
     * 获取给定长度的随机数字构成的字符串
     * @param $length
     * @return string
     */
    public static function getRandCode($length) {
        $str = "0123456789";
        $len = strlen($str);
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $str[mt_rand() % $len];
        }
        return $code;
    }

    /**
     * 获取数组数据签名
     * @param $rows
     * @param string $secret
     * @return string
     */
    public static function getRowsSign($rows, $secret = '') {
        if (empty($rows)) {
            return null;
        }
        $strArr = [];
        foreach ($rows as $k => $r) {
            $strArr[] = sprintf("%s=%s", strval($k), strval($r));
        }
        return md5(md5(join(",", $strArr)) . "_" . $secret);
    }

    /**
     * 检查手机号
     * @param $mobile
     * @return mixed
     */
    public static function checkMobile($mobile) {
        $result = filter_var($mobile, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^1[0-9]{10,10}$/')));
        return $result;
    }

    /**
     * 检查url地址是否正确
     * @param $url
     * @return bool
     */
    public static function checkUrl($url){
        if(preg_match('#^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?#i', $url)){
            return true;
        }else{
            return false;
        }
    }

    // 获取客户端IP地址
    static function getClientIP() {
        static $ip = NULL;
        if ($ip !== NULL)
            return $ip;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        //
        $env = get_cfg_var('LOCALENV');
        if (('test' == $env || 'beta' == $env) && !empty($_REQUEST['clientIp'])) {
            $ip = $_REQUEST['clientIp'];
        }
        // IP地址合法验证
        $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
        return $ip;
    }

    /**
     * 判断两个整数是否相等
     * @param $intA
     * @param $intB
     * @return bool
     */
    public static function equal($intA, $intB) {
        if (round($intA) == round($intB)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 序列化对象
     * @param $obj
     * @return string
     */
    public static function enSerialize($obj) {
        return base64_encode(gzcompress(serialize($obj)));
    }

    /**
     * 反序列化字符串
     * @param string $str 字符串
     * @return mixed
     */
    public static function deSerialize($str) {
        return unserialize(gzuncompress(base64_decode($str)));
    }

    /**
     * 将内容进行UNICODE编码，编码后的内容格式：\u56fe\u7247 （原始：图片）
     * @param $name
     * @return string
     */
    public static function unicodeEncode($name) {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2) {
            $c = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0) {    // 两个字节的文字
                $str .= '\u' . base_convert(ord($c), 10, 16) . base_convert(ord($c2), 10, 16);
            } else {
                $str .= $c2;
            }
        }
        return $str;
    }

    /**
     * 将UNICODE编码后的内容进行解码，编码后的内容格式：\u56fe\u7247 （原始：图片）
     * @param $name
     * @return string
     */
    public static function unicodeDecode($name) {
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches)) {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++) {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0) {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code) . chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                } else {
                    $name .= $str;
                }
            }
        }
        return $name;
    }

    /**
     * 格式化信用卡过期码
     * @param $expire_time
     * @return string
     */
    public static function formatExp($expire_time) {
        if (!empty($expire_time)) {
            $expire_times = explode("-", $expire_time);
            if (count($expire_times) == 1 && strlen($expire_time) == 4) {
                return $expire_time;
            }
            if (strlen($expire_times[0]) == 4) {
                $year = substr($expire_times[0], 2, 4);
            } else {
                $year = $expire_times[0];
            }
            $mon = $expire_times[1];
            if (intval($mon) < 10) {
                return "0" . intVal($mon).$year;
            } else {
                return $mon.$year;
            }
        } else {
            return "";
        }
    }

    /**
     * 支付签名
     * @param string $clientKey 客户端秘钥
     * @param string $token 业务token
     * @param string $url 请求地址如: coupon/detail
     * @param array $params 请求数据
     * @return string
     */
    public static function encodeSign($clientKey,$token, $url, $params) {
        if(empty($token)){
            $token='';
        }
        $params["gsxpay_url"] = $url;
        ksort($params);
        $strArr = [];
        foreach ($params as $key => $value) {
            $strArr[] = $key . "#" . rawurlencode($value);//对空格的处理，urlencode处理成“+”，rawurlencode处理成“%20”
        }
        $str = sprintf("token=%s,values=%s", $token, join(",", $strArr));
        Log::w(['token'=>$token,'params'=>$params]);
        return md5(sprintf("%s,%s",md5($str),$clientKey));
    }

    /**
     * 通知签名
     * @param array $param 参数
     * @param string $secret 商户秘钥
     * @return string
     */
    public static function notifySign($param,$secret){
        $strArr = [];
        //签名
        ksort($param);
        foreach($param as $key=>$val){
            if(is_null($val)){
                continue;
            }
            $strArr[] = $key . "#" . json_encode(strval($val));
        }
        $str = sprintf("values=%s", join(",", $strArr));
        $sign = md5(md5($str)."_".$secret);
        return $sign;
    }

    /**
     * 将xml转为array
     * @param $xml
     * @return mixed
     */
    public static function xmlToArray($xml) {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }


    /**
     *    作用：array转xml
     */
    public  static  function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 打码字符串，中间1/3的字符串打码处理
     * @param $string
     * @return string
     */
    public static function maskString($string){
        $len = mb_strlen($string);
        $start = $end = floor($len/3);
        if($len>12){
            //如果长度>12位，留前4位，后4位，中间全部打码
            $start = 4;
            $end = 4;
        }
        return self::maskStr($string,$start,$end);
    }

    /**
     * 获取当前时间，格式：Y-m-d H:i:s
     * @return bool|string
     */
    public static function getCurrentTime(){
        return date("Y-m-d H:i:s");
    }



    /**
     * 模糊任意字符串
     *
     * @param string  $string 字符串
     * @param integer $start  起始处
     * @param integer $end    结束处
     *
     * @return string
     */
    public static function maskStr($string, $start, $end)
    {
        $count = max(mb_strlen($string) - $start - $end, 0);
        $mask = $count ? str_repeat('*', $count) : '';
        $p1 = mb_substr($string, 0, $start);
        $p2 = mb_substr($string, max($start, mb_strlen($string) - $end));
        return $p1.$mask.$p2;
    }


    /**
     * 模糊数组中的字段
     *
     * @param $data 需加密的数据
     * @param $dep 需加密的数组深度
     * @param $filter 需加密的key
     * @return mixed
     */
    static function maskArray($data, $filter,$dep = null) {
        if ($dep <= 0) {
            return $data;
        }
        if(!$dep){
            $dep = self::array_depth($data);
        }
        foreach ($data as $key => $sub_data) {
            if (is_array($sub_data)) {
                $data[$key] = self::maskArray($sub_data, $filter,$dep - 1);
            } elseif (in_array($key,$filter) && $sub_data && is_string($sub_data)) {
                $maskLength = intval(mb_strlen($sub_data) / 2);
                $internal = intval(mb_strlen($sub_data) / 3);
                $internal = $internal < 1 ? 1 : $internal;
                $internal = $internal > 6 ? 6 : $internal; //加密12个字符 最多
                $data[$key] = self::maskStr($sub_data, intval(mb_strlen($sub_data) - $maskLength - $internal), intval($maskLength - $internal));
            }else{
                $data[$key] = $sub_data;
            }
        }
        return $data;
    }


    /**
     * 获取数组深度
     * @param $array
     * @return int
     */
    public static function array_depth($array) {
        $max_depth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::array_depth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }
        return $max_depth;
    }



    /**
     * 记录转成table
     * @param array $rows 记录数组
     * @param bool $even 是否在奇数行增加even样式
     * @return string
     */
    public static function getRowsTable($rows,$even=false){
        if(empty($rows)){
            return '';
        }
        //表头
        $keys = [];
        if(isset($rows[0])){
            $keys = array_keys($rows[0]);
        }
        if(empty($keys) && isset($rows[1])){
            $keys = array_keys($rows[1]);
        }
        if(empty($keys)){
            return '';
        }
        $table = '<table>';
        $th = "<tr>";
        foreach($keys as $key){
            $th.='<th>'.$key."</th>";
        }
        $th.="</tr>";
        $table.=$th;
        //内容
        $index = 1;
        foreach($rows as $row){
            if($even && $index%2==0){
                $tr="<tr class='even'>";
            }else{
                $tr="<tr>";
            }
            foreach($keys as $key){
                $tr.="<td>".$row[$key]."</td>";
            }
            $tr.="</tr>";
            $table.=$tr;
            $index++;
        }
        $table.="</table>";
        return $table;
    }


    /**
     * 获取html代码
     * @param string $body 内容
     * @param string $title 标题
     * @return string
     */
    public static function makeHtml($body,$title){
        $html = '
        <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>'.$title.'</title>
                    <style>
                        table {
                            background-color: #FFF;
                            border-collapse: collapse;
                            font-size:12px;
                        }
                        th,
                        td {
                            border: 1px solid #E9E9E9;
                            padding: 10px;
                        }
                        th {
                            background-color: #FFF4D4;
                        }
                        td {
                            text-align: center;
                            color: #555;
                        }
                         tr.even {
                            background-color: #F5F5F5;
                        }
                    </style>
                </head>
            <body>
        ';
        $html.=$body;
        $html.='</body></html>';
        return $html;
    }


    /**
     * 从文件流中获取xml  并且从xml中获取数组
     * @return array
     */
    public static function getXml2ArrayFromInput() {
        $xmlData = file_get_contents("php://input");
        if ($xmlData) {
            $postObj = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!is_object($postObj)) {
                return false;
            }
            $array = json_decode(json_encode($postObj), true); // xml对象转数组
            return array_change_key_case($array, CASE_LOWER); // 所有键小写
        } else {
            return false;
        }
    }

    /**
     * 循环获取array_key
     *
     * @param $data
     * @param $dep
     */
    public static function array_keys_recursive($data, $dep) {
        $keys = array_keys($data);
        if ($dep > 1) {
            foreach ($data as $sub_data) {
                if (is_array($sub_data)) {
                    $keys = array_merge($keys, self::array_keys_recursive($sub_data, $dep - 1));
                }
            }
        }
        return $keys;
    }

}