<?php
/**
 * remark
 * User: myf
 * Date: 17/3/14
 * Time: 下午1:06
 */

namespace Myf\Libs;


class File {


    /**
     * 写文件
     * @param $filename
     * @param $content
     * @return bool
     */
    public static function write($filename, $content) {
        $dir = dirname($filename);
        is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0777));
        $fp = @fopen($filename, "w");
        if (!$fp) {
            return false;
        } else {
            fwrite($fp, $content);
            fclose($fp);
            return true;
        }
    }

    /**
     * 读取文件
     * @param String $filename 文件绝对路径
     * @return String 内容
     */
    public static function read($filename) {
        $fp = @fopen($filename, "r");
        if (!$fp) {
            return null;
        } else {
            $content = fread($fp, filesize($filename));
            fclose($fp);
            return $content;
        }
    }

    /**
     * 删除文件
     * @param String $filename 文件绝对路径
     * @return boolean 删除成功返回true，返回失败返回false
     */
    public static function delete($filename) {
        $res = @unlink($filename);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 循环删除目录及子文件
     * @param $pathdir
     */
    public static function deleteTree($pathdir) {
        if (self::isEmptyDir($pathdir)) {//如果是空的
            @rmdir($pathdir);
            //直接删除
        } else {//否则读这个目录，除了.和..外
            $d = dir($pathdir);
            while ($a = $d->read()) {
                if (is_file($pathdir . '/' . $a) && ($a != '.') && ($a != '..')) {
                    @unlink($pathdir . '/' . $a);
                }
                //如果是文件就直接删除
                if (is_dir($pathdir . '/' . $a) && ($a != '.') && ($a != '..')) {//如果是目录
                    if (!self::isEmptyDir($pathdir . '/' . $a)) {//是否为空
                        //如果不是，调用自身，不过是原来的路径+他下级的目录名
                        self::deltree($pathdir . '/' . $a);
                    }
                    if (self::isEmptyDir($pathdir . '/' . $a)) {//如果是空就直接删除
                        @rmdir($pathdir . '/' . $a);
                    }
                }
            }
            $d->close();
            @rmdir($pathdir);
        }
    }

    /**
     * 判断目录是否为空，我的方法不是很好吧？只是看除了.和..之外有其他东西不是为空
     * @param $pathDir
     * @return bool
     */
    public static function isEmptyDir($pathDir) {
        $d = @opendir($pathDir);
        $i = 0;
        while ($a = @readdir($d)) {
            $i++;
        }
        @closedir($d);
        if ($i > 2) {
            return false;
        } else{
            return true;
        }
    }

    /**
     * 读取文件目录下的文件名
     * @param $dir
     * @param string $pattern
     * @return array
     */
    public static function dirList($dir, $pattern = "") {
        $arr = array();
        $dir_handle = opendir($dir);
        if ($dir_handle) {
            // 这里必须严格比较，因为返回的文件名可能是“0”
            while (($file = readdir($dir_handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $tmp = realpath($dir . '/' . $file);
                if (is_dir($tmp)) {
                    $retArr = self::dirList($tmp, $pattern);
                    if (!empty($retArr)) {
                        $arr[] = $file;
                    }
                } else {
                    if ($pattern === "" || preg_match($pattern, $tmp)) {
                        $arr[] = $file;
                    }
                }
            }
            closedir($dir_handle);
        }
        return $arr;
    }

    /**
     * 写出数组到文件
     * @param string $file 绝对地址文件名
     * @param $data Array
     * @return int
     */
    public static function writeArray($file, $data) {
        $dir = dirname($file);
        is_dir($dir) or ( createFolders(dirname($dir)) and mkdir($dir, 0777));
        $data = '<?php return ' . var_export($data, TRUE) . ';';
        return file_put_contents($file, $data);
    }

    /**
     * 读取数组文件
     * @param $file
     * @return array|mixed
     */
    public static function readArray($file) {
        if (file_exists($file)) {
            $data = include $file;
            return $data;
        }
        return array();
    }

    /**
     * 写入数组缓存
     * @param string $key 缓存key
     * @param $data Array 数组
     */
    public static function writeArrayCache($key, $data) {
        $file = CACHE_PATH . "/" . $key . '.php';
        self::writeArray($file, $data);
    }

    /**
     * 读取数组缓存
     * @param string $key 缓存key
     * @return array|mixed
     */
    public static function readArrayCache($key) {
        $file = CACHE_PATH . $key . '.php';
        return self::readArray($file);
    }


}