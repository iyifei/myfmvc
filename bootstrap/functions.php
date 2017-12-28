<?php
/**
 * 全局通用函数
 * User: www.minyifei.cn
 * Date: 17/2/22
 * Time: 下午8:20
 */
use Myf\Constants\ErrorCode;
use Myf\Libs\CmsException;
use Myf\Libs\DB;
use Myf\Libs\Utils;

/**
 * 获取纯字符串
 * @param $name
 * @return null
 */
function getUrlString($name) {
    $value = filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRIPPED);
    if ($value) {
        return trim($value);
    } else {
        return null;
    }
}

/**
 * 获取目录下的文件名称
 * @param $path
 * @return array
 */
function dir_files($path) {
    $files = [];
    foreach (scandir($path) as $file) {
        if ($file != '.' && $file != '..') {
            $item = [ 'file' => $path . '/' . $file, 'name' => $file ];
            $files[] = $item;
        }
    }
    return $files;
}

/**
 * mvc路由解析器,负责从参数中提取url路由信息
 */
function getMvcRoute() {
    //控制器
    $c = getUrlString("c");
    //执行方法
    $a = getUrlString("a");
    //url rewrite 读取路由,如 teacher_center/profile解析为$c=teacher_center,$a=profile
    $s = getUrlString("_url");
    $urls = array();
    if (!empty($s)) {
        $s = trim(str_replace("/", " ", $s));
        $urls = explode(" ", $s);
        if (isset($urls[0])) {
            $c = $urls[0];
        }
        if (isset($urls[1])) {
            $a = $urls[1];
        }
    }
    //默认访问方法为 index
    if (empty($a)) {
        $a = "index";
    }
    //默认控制器为 index
    if (empty($c)) {
        $c = "index";
    }
    //路由
    $route = array(
        "_a" => $a, "_c" => $c, "_urls" => $urls,
    );
    //转换 _c = 'ab_cd' 为 _c='AbCd'
    $cs = explode("_", $c);
    session("_urls", array( "_c" => $c, "_a" => $a ));
    for ($index = 0; $index < count($cs); $index++) {
        $cs[$index] = mb_ucfirst($cs[$index]);
    }
    $c = implode("", $cs);
    $route["a"] = $a;
    $route["c"] = $c;
    return $route;
}

/**
 * 读取配置文件内容
 * @param string $name
 * @return null
 */
function config($name = null) {
    global $_gblConfig;
    $nameArr = explode('.', $name);
    $fName = current($nameArr);
    $res = null;
    if (isset($_gblConfig[$fName])) {
        unset($nameArr[0]);
        $res = $_gblConfig[$fName];
        foreach ($nameArr as $ne) {
            if (isset($res[$ne])) {
                $res = $res[$ne];
            } else {
                $res = null;
                break;
            }
        }
    }
    return $res;
}

/**
 * 自动加载类
 * @param String $className 类名
 * @throws Exception
 */
function loader($className) {
    $file = getClassFile($className);
    if (is_file($file)) {
        require_once($file);
    }
}

/**
 * 获取类绝对文件路径
 * @global $namespaces
 * @param $className
 * @return string
 */
function getClassFile($className) {
    global $_gblNamespaces;
    $names = explode("\\", $className);
    $class = array_pop($names);
    $key = join("\\", $names);
    //系统命名空间
    $path = $_gblNamespaces[$key];
    $file = $path . "/" . $class . ".php";
    return $file;
}


/**
 * 驼峰命名转下划线命名，如 UserName => user_name
 * @param string $s
 * @return string
 */
function toUnderLineName($s) {
    $s = lcfirst($s);
    $chars = str_split($s);
    $res = "";
    foreach ($chars as $c) {
        if (isCapitalLetter($c)) {
            $c = "_" . strtolower($c);
        }
        $res .= $c;
    }
    return $res;
}


/**
 * 获取类的文件名
 * @global type $namespaces
 * @param type $className
 * @return type
 */
function getClassFileName($className) {
    global $namespaces;
    $names = explode("\\", $className);
    $class = array_pop($names);
    return $class;
}


/**
 * 判断字符串是否为大写字母
 * @param type $c
 * @return boolean
 */
function isCapitalLetter($c) {
    if (preg_match('/^[A-Z]+$/', $c)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 循环创建文件夹
 * @param $dir
 * @return bool
 */
function createFolders($dir) {
    return is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0777));
}


/**
 * 首字母大写
 * @param $string
 * @param string $e
 * @return string
 */
function mb_ucfirst($string, $e = 'utf-8') {
    if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) {
        $string = mb_strtolower($string, $e);
        $upper = mb_strtoupper($string, $e);
        preg_match('#(.)#us', $upper, $matches);
        $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
    } else {
        $string = ucfirst($string);
    }
    return $string;
}

/**
 * 获取当前时间毫秒数
 * @return float
 */
function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

/**
 * 获取当前时间
 * @return bool|string
 */
function getCurrentTime() {
    return date("Y-m-d H:i:s");
}


/**
 * 获取项目基础相对URL
 * @return string
 */
function getBasePath() {
    $sitePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($sitePath == "/" || $sitePath == "\\") {
        $sitePath = "";
    }
    return $sitePath;
}

function getBaseURL() {
    return getBasePath();
}

/**
 * 获取项目基础绝对URL
 * @return string
 */
function getFullURL() {
    $pageURL = 'http://';
    $sitePath = getBasePath();
    $host = $_SERVER["HTTP_HOST"];
    $port = $_SERVER["SERVER_PORT"];
    if ($port != "80") {
        $pageURL .= $host . $sitePath;
    } else {
        $pageURL .= str_replace(":80", "", $host) . $sitePath;
    }
    return $pageURL;
}

/**
 * 获取项目相对URL路径
 * @return null
 */
function getProjectURL() {
    $sysPath = dirname(dirname(__FILE__));
    $cwd = getcwd();
    $cwd = str_replace("\\", "/", $cwd);
    $sysPath = str_replace("\\", "/", $sysPath);
    $filepath = str_replace($sysPath, "", $cwd);
    $url = str_replace($filepath, "", getFullURL());
    return $url;
}


/**
 * session管理
 * @param $name
 * @param string $value
 */
function session($name, $value = '') {
    $prefix = "_kuxue_cms_";
    if ('' === $value) {
        if (0 === strpos($name, '[')) {// session 操作
            if ('[pause]' == $name) {// 暂停session
                session_write_close();
            } elseif ('[start]' == $name) {// 启动session
                session_start();
            } elseif ('[destroy]' == $name) {// 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) {// 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) {// 检查session
            $name = substr($name, 1);
            if ($prefix) {
                return isset($_SESSION[$prefix][$name]);
            } else {
                return isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) {// 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) {// 获取session
            if (isset($_SESSION[$prefix][$name])) {
                return $_SESSION[$prefix][$name];
            } else {
                return null;
            }
        } else {
            return $_SESSION[$name];
        }
    } elseif (is_null($value)) {// 删除session
        if ($prefix) {
            unset($_SESSION[$prefix][$name]);
        } else {
            unset($_SESSION[$name]);
        }
    } else {// 设置session
        if ($prefix) {
            if (isset($_SESSION[$prefix]) && !is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }
}

/**
 * Cookie 设置、获取、删除
 * @param $name
 * @param string $value
 * @param null $option
 * @return null|void
 */
function cookie($name, $value = '', $option = null) {
    // 默认设置
    $config = array(
        'prefix' => "_kuxue_cms_", // cookie 名称前缀
        'expire' => '36000', // cookie 保存时间
        'path'   => '.', // cookie 保存路径
        'domain' => null, // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (!empty($option)) {
        if (is_numeric($option)) $option = array( 'expire' => $option ); elseif (is_string($option)) parse_str($option, $option);
        $config = array_merge($config, array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE)) return;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }
    $name = $config['prefix'] . $name;
    if ('' === $value) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        // 获取指定Cookie
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]);
            // 删除指定cookie
        } else {
            // 设置cookie
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}


/**
 * 对象输出
 * @param array|string|int|double $var
 * @param boolean $echo
 * @param string $label
 * @param boolean $strict
 * @return string|null
 */
function dump($var, $echo = true, $label = null, $strict = false) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else {
        return $output;
    }
}

/**
 * 获取客户端IP
 * @return null
 */
function getClientIP() {
    static $ip = null;
    if ($ip !== null) {
        return $ip;
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}


/**
 * GET请求
 * @param String $name 变量
 * @param $default 默认值
 * @return value
 */
function get($name, $default = null) {
    if (isset($_GET[$name])) {
        $value = $_GET[$name];
    } else {
        $value = $default;
    }
    return trim($value);
}


/**
 * 读取POST值
 * @param String $name 变量
 * @param $default 默认值
 * @return value
 */
function post($name, $default = null) {
    if (isset($_POST[$name])) {
        $value = $_POST[$name];
    } else {
        $value = $default;
    }
    return trim($value);
}


/**
 * 读取请求数据
 * @param String $name
 * @param $default null
 * @return value
 */
function request($name, $default = null) {
    if (isset($_REQUEST[$name])) {
        $value = $_REQUEST[$name];
    } else {
        $value = $default;
    }
    return trim($value);
}


/**
 * 读取不为null的请求数据
 * @param $name
 * @return mixed
 */
function requestNotEmpty($name) {
    if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name])) {
        $value = $_REQUEST[$name];
        return $value;
    } else {
        CmsException::throwExp($name . " is not empty", ErrorCode::PARAM_ERROR);
    }
}

/**
 * 获取Integer变量
 * @param String $name
 * @param $default null
 * @return NULL|number
 */
function getInteger($name, $default = null) {
    if (isset($_REQUEST[$name]) && is_numeric($_REQUEST[$name])) {
        $value = intval($_REQUEST[$name]);
    } else {
        $value = $default;
    }
    return $value;
}

/**
 * 获取Double变量
 * @param String $name
 * @param $default null
 * @return NULL|number
 */
function getDouble($name, $default = null) {
    if (isset($_REQUEST[$name]) && is_numeric($_REQUEST[$name])) {
        $value = doubleval($_REQUEST[$name]);
    } else {
        $value = $default;
    }
    return $value;
}

/**
 * 字符串加密
 * @param string $original
 * @param string $secret 秘钥
 * @return string
 */
function encodePassword($original, $secret = 'ZqK2etJM') {
    $encoder = md5($secret . md5(base64_encode($original . "_myf_yht")));
    return $encoder;
}

/**
 * 解析url的参数
 * @param String $query
 * @return Array 解析后返回key-value对象
 */
function parseUrlParams($query) {
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

/**
 * 读取数组的值
 * @param $params
 * @param $key
 * @return null
 */
function getParamsValue($params, $key) {
    if (isset($params[$key])) {
        return $params[$key];
    } else {
        return null;
    }
}

/**
 * 查找指定元素的所有父类元素
 * @param $data 数组
 * @param $id 当前id
 * @param string $pname
 * @return array
 */
function parentTrees($data, $id, $pname = "pid") {
    $tree = array();
    if (is_array($data)) {
        foreach ($data as $value) {
            if ($value["id"] == $id) {
                if ($value[$pname] > 0) {
                    $tree = parentTrees($data, $value[$pname], $pname);
                }
                $tree[] = $value;
            }
        }
    }
    return $tree;
}

function childTree($data, $pid = 0, $pname = "pid") {
    $tree = array();
    foreach ($data as $value) {
        if ($value[$pname] == $pid) {
            $value["childs"] = childTree($data, $value["id"]);
            $tree[] = $value;
        }
    }
    return $tree;
}

/**
 * 生成树
 * @param $data
 * @param int $pid
 * @param string $pname
 * @return array
 */
function makeTree($data, $pid = 0, $pname = "pid") {
    $tree = array();
    foreach ($data as $key => $value) {
        if ($value[$pname] == $pid) {
            $value["childs"] = makeTree($data, $value["id"], $pname);
            $tree[] = $value;
        }
    }
    return $tree;
}

/**
 * 清除xss攻击
 * @param $string
 * @param bool $low
 * @return bool
 */
function clean_xss(&$string, $low = false) {
    if (!is_array($string)) {
        $string = trim($string);
        $string = strip_tags($string);
        $string = htmlspecialchars($string);
        if ($low) {
            return true;
        }
        $string = str_replace(array( '"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string);
        $no = '/%0[0-8bcef]/';
        $string = preg_replace($no, '', $string);
        $no = '/%1[0-9a-f]/';
        $string = preg_replace($no, '', $string);
        $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
        $string = preg_replace($no, '', $string);
        return true;
    }
    $keys = array_keys($string);
    foreach ($keys as $key) {
        clean_xss($string [$key]);
    }
}

/**
 * 读取当前页面的url
 * @return string
 */
function getCurrPageURL() {
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

/**
 * 获取唯一标识
 * @return string
 */
function getUUID() {
    $str = uniqid(microtime(true), true);
    return md5($str);
}

/**
 * 读取文章描述
 * @param $body
 * @param int $length
 * @return string
 */
function getDescription($body, $length = 150) {
    $body = strip_tags($body);
    return utf8_strcut($body, 0, $length);
}

function utf8_strcut($str, $start, $length = null) {
    preg_match_all('/./us', $str, $match);
    $chars = is_null($length) ? array_slice($match[0], $start) : array_slice($match[0], $start, $length);
    unset($str);
    return implode('', $chars);
}

/**
 * 字符串中的br转nl
 * @param $text
 * @return mixed
 */
function br2nl($text) {
    $text = preg_replace('/<br\\s*?\/??>/i', chr(13), $text);
    $text = preg_replace('/<p\\s*?\/??>/i', chr(13), $text);
    return preg_replace('/&nbsp;/i', ' ', $text);
}

/**
 * 检查url格式
 * @param $url
 * @return bool
 */
function checkUrl($url) {
    if (!preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $url)) {
        return false;
    }
    return true;
}

/**
 * 补全url
 * @param $url 当前页面网址
 * @param $str 需要补全的相对地址，也可以是数组
 * @return array|mixed|string
 */
function formatUrl($url, $str) {
    if (is_array($str)) {
        $return = array();
        foreach ($str as $href) {
            $return[] = formatUrl($url, $href);
        }
        return $return;
    } else {
        if (stripos($str, 'http://') === 0 || stripos($str, 'ftp://') === 0) {
            return $str;
        }
        $str = str_replace('\\\\', '/', $str);
        $parseUrl = parse_url(dirname($url) . '/');
        $scheme = isset($parseUrl['scheme']) ? $parseUrl['scheme'] : 'http';
        $host = $parseUrl['host'];
        $path = isset($parseUrl['path']) ? $parseUrl['path'] : '';
        $port = isset($parseUrl['port']) ? $parseUrl['port'] : '';

        if (strpos($str, '/') === 0) {
            return $scheme . '://' . $host . $str;
        } else {
            $part = explode('/', $path);
            array_shift($part);
            $count = substr_count($str, '../');
            if ($count > 0) {
                for ($i = 0; $i <= $count; $i++) {
                    array_pop($part);
                }
            }
            $path = implode('/', $part);
            $str = str_replace(array( '../', './' ), '', $str);
            $path = $path == '' ? '/' : '/' . trim($path, '/') . '/';
            return $scheme . '://' . $host . $path . $str;
        }
    }

}

/**
 * 组织成easyui tree格式
 * request data = [[{"id":"1","text":"text","pid":0,"iconCls":"icon_home_page"}]]
 */
function easyuitree($treedata, $pid = 0) {
    $tree = array();
    foreach ($treedata as $value) {
        $vpid = $value["pid"];
        if ($vpid == $pid) {
            $value["children"] = easyuitree($treedata, $value["id"]);
            $tree[] = $value;
        }
    }
    return $tree;
}

/**
 * 页面跳转
 * @param string $url
 */
function jumpUrl($url) {
    header("Location:" . $url);
}


//获取控制器名称
function getControllerName() {
    $urls = session("_urls");
    return $urls["_c"];
}

//获取访问方法名称
function getActionName() {
    $urls = session("_urls");
    return $urls["_a"];
}

/**
 * 读取一个header的值
 * @param $name
 * @param null $headers
 * @return null
 */
function getHeader($name, $headers = null) {
    if (!isset($headers)) {
        $headers = getAllHeaders();
    }
    if (isset($headers[$name])) {
        return $headers[$name];
    } else {
        return null;
    }
}

/**
 * 读取所有的header信息
 * @return array
 */
function getAllHeaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
        }
    }
    return $headers;
}


/**
 * json encode 处理中文显示出
 * @param $array
 * @return mixed|string
 */
function jsonCNEncode($array) {
    $json = json_encode($array, JSON_UNESCAPED_UNICODE);
    $json = str_replace('\"', '"', $json);
    $json = str_replace('\\', '', $json);
    return $json;
}

/**
 * 检查手机号格式
 * @param $mobile
 * @return bool
 */
function checkMobile($mobile) {
    $result = filter_var($mobile, FILTER_VALIDATE_REGEXP, array( 'options' => array( 'regexp' => '/^1[0-9]{10,10}$/' ) ));
    if ($result) {
        return true;
    } else {
        return false;
    }
}

/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id 数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root = 'kuxue', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8') {
    if (is_array($attr)) {
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr = trim($attr);
    $attr = empty($attr) ? '' : " {$attr}";
    $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml .= "<{$root}{$attr}>";
    $xml .= data_to_xml($data, $item, $id);
    $xml .= "</{$root}>";
    return $xml;
}

/**
 * 数据XML编码
 * @param mixed $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id 数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item = 'item', $id = 'id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if (is_numeric($key)) {
            $id && $attr = " {$id}=\"{$key}\"";
            $key = $item;
        }
        $xml .= "<{$key}{$attr}>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml .= "</{$key}>";
    }
    return $xml;
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time = 0, $msg = '') {
    //多行URL地址支持
    $url = str_replace(array( "\n", "\r" ), '', $url);
    if (empty($msg)) $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0) $str .= $msg;
        exit($str);
    }
}


/**
 * 选择table
 * @param null $tableName 表名
 * @param null $dbName 数据库名称
 * @return Myf\Libs\DB;
 */
function table($tableName = null, $dbName = null) {
    $db = database($dbName);
    if ($tableName) {
        $db->table($tableName);
    }
    return $db;
}

/**
 * 选择数据库
 * @param null $dbName 数据库链接名称
 * @return Myf\Libs\DB
 */
function database($dbName = null) {
    if (empty($dbName)) {
        $dbName = getDefaultDbName();
    }
    $db = DB::getInstance($dbName);
    return $db;
}

/**
 * 获取默认数据库名
 * @return null
 */
function getDefaultDbName() {
    return config('db.default_db');
}

function shutdown() {
    $returnData = session("returnData");
    if (empty($returnData)) {
        $returnData = [];
    }
    Utils::logResponse($returnData);
}

/**
 * 压缩html代码
 * @param $html_source
 * @return string
 */
function compress_html($html_source) {
    return ltrim(rtrim(preg_replace(array( "/> *([^ ]*) *</", "//", "'/\*[^*]*\*/'", "/\r\n/", "/\n/", "/\t/", '/>[ ]+</' ), array( ">\\1<", '', '', '', '', '', '><' ), $html_source)));
}

/**
 * 判断是否为https
 * @return bool
 */
function is_HTTPS() {
    if (!isset($_SERVER['HTTPS'])) return false;
    if ($_SERVER['HTTPS'] === 1) {  //Apache
        return true;
    } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
        return true;
    } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
        return true;
    }
    return false;
}


if (!function_exists('array_column')) {
    function array_column($input, $columnKey, $indexKey = null) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array)$input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : $row;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}

/**
 * 大小转换，b转换为 KB,MB,GB,TB
 * @param $size
 * @return string
 */
function formatBytes($size) {
    $units = array( ' B', ' KB', ' MB', ' GB', ' TB' );
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2) . $units[$i];
}

/**
 *      把秒数转换为时分秒的格式
 * @param Int $times 时间，单位 秒
 * @return String
 */
function secToTime($times) {
    $result = '00:00:00';
    if ($times > 0) {
        $hour = floor($times / 3600);
        if ($hour < 10 && $hour >= 0) {
            $hour = '0' . $hour;
        }
        $minute = floor(($times - 3600 * $hour) / 60);
        if ($minute < 10 && $minute >= 0) {
            $minute = "0" . $minute;
        }
        $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);
        if ($second < 10) {
            $second = "0" . $second;
        }
        $result = $hour . ':' . $minute . ':' . $second;
    }
    return $result;
}

/**
 * 时间格式转为秒
 * @param String $time XX:XX:XX
 * @return int 秒
 */
function timeToSec($time) {
    $sp = explode(":", $time);
    $sec = intval($sp[0]) * 3600 + intval($sp[1]) * 60 + intval($sp[2]);
    return $sec;
}

/**
 * uuid过滤sql注入
 * @param $uuidArr
 * @return mixed
 */
function uuidArrFilter($uuidArr) {
    $arr = [];
    foreach ($uuidArr as $uuid) {
        if (!empty($uuid)) {
            $uuid = addslashes($uuid);
            $arr[] = $uuid;
        }
    }
    return $arr;
}


//打印日志
function plog($str) {
    echo sprintf("[%s] %s\n", getCurrentTime(), $str);
}

/**
 * 获取服务器端ip
 * @return mixed
 */
function getServerIp() {
    $ips = swoole_get_local_ip();
    return reset($ips);
}

/**
 * 数组中删除值
 * @param $arr
 * @param $var
 */
function array_remove_value(&$arr, $var) {
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            array_remove_value($arr[$key], $var);
        } else {
            $value = trim($value);
            if ($value == $var) {
                unset($arr[$key]);
            } else {
                $arr[$key] = $value;
            }
        }
    }
}


// 两个日期之间的所有日期
function prDates($start, $end) {
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    $days = [];
    while ($dt_start <= $dt_end) {
        $days[] = date('Y-m-d', $dt_start);
        $dt_start = strtotime('+1 day', $dt_start);
    }
    return $days;
}

/**
 * 获取指定日期之间的各个周
 * @param $sdate
 * @param $edate
 * @return array
 */
function get_weeks($sdate, $edate) {
    $range_arr = array();
    // 检查日期有效性
    $this->check_date(array( $sdate, $edate ));
    // 计算各个周的起始时间
    do {
        $weekinfo = $this->get_weekinfo_by_date($sdate);
        $end_day = $weekinfo['week_end_day'];
        $start = $weekinfo['week_start_day'];
        $end = $weekinfo['week_end_day'];
        $range = [ 'start' => $start, 'end' => $end, 'name' => $this->substr_date($start) . '~' . $this->substr_date($end) ];
        $range_arr[] = $range;
        $sdate = date('Y-m-d', strtotime($sdate) + 7 * 86400);
    } while ($end_day < $edate);
    return $range_arr;
}

/**
 * 根据指定日期获取所在周的起始时间和结束时间
 */
function get_weekinfo_by_date($date) {
    $idx = strftime("%u", strtotime($date));
    $mon_idx = $idx - 1;
    $sun_idx = $idx - 7;
    return array(
        'week_start_day' => strftime('%Y-%m-%d', strtotime($date) - $mon_idx * 86400), 'week_end_day' => strftime('%Y-%m-%d', strtotime($date) - $sun_idx * 86400),
    );
}

/**
 * 截取日期中的月份和日
 * @param string $date
 * @return string $date
 */
function substr_date($date) {
    if (!$date) return false;
    return date('m-d', strtotime($date));
}

/**
 * 检查日期的有效性 YYYY-mm-dd
 * @param array $date_arr
 * @return boolean
 */
function check_date($date_arr) {
    $invalid_date_arr = array();
    foreach ($date_arr as $row) {
        $timestamp = strtotime($row);
        $standard = date('Y-m-d', $timestamp);
        if ($standard != $row) $invalid_date_arr[] = $row;
    }
    if (!empty($invalid_date_arr)) {
        die("invalid date -> " . print_r($invalid_date_arr, true));
    }
}

/**
 * 获取某年第几周的开始日期和结束日期
 * @param int $year
 * @param int $week 第几周;
 */
function weekday($year, $week = 1) {
    $year_start = mktime(0, 0, 0, 1, 1, $year);
    $year_end = mktime(0, 0, 0, 12, 31, $year);

    // 判断第一天是否为第一周的开始
    if (intval(date('W', $year_start)) === 1) {
        $start = $year_start;//把第一天做为第一周的开始
    } else {
        $week++;
        $start = strtotime('+1 monday', $year_start);//把第一个周一作为开始
    }

    // 第几周的开始时间
    if ($week === 1) {
        $weekday['start'] = $start;
    } else {
        $weekday['start'] = strtotime('+' . ($week - 0) . ' monday', $start);
    }

    // 第几周的结束时间
    $weekday['end'] = strtotime('+1 sunday', $weekday['start']);
    if (date('Y', $weekday['end']) != $year) {
        $weekday['end'] = $year_end;
    }
    $weekday['start'] = date("Y-m-d", $weekday['start']);
    $weekday['end'] = date("Y-m-d", $weekday['end']);
    return $weekday;
}