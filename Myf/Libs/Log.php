<?php
/**
 * 日志输出文件
 * User: myf
 * Date: 17/2/23
 * Time: 上午6:25
 */

namespace Myf\Libs;


class Log {

    const ERROR = "ERROR";
    const DEBUG = "DEBUG";
    const SQL = "SQL";
    const NOTICE = "NOTICE";

    // 日志记录方式
    const SYSTEM = 0;
    const MAIL = 1;
    const TCP = 2;
    const FILE = 3;

    //日志信息
    static $logs = array();
    //日期格式
    static $format = '[ c ]';

    /**
     * 直接输出日志
     * @param string $message 日志内容
     * @param string $level 日志输出级别
     * @param int $type 存储日志类型
     * @param string $file 写入文件位置
     * @param string $extra 额外参数
     */
    public static function write($message, $level = self::DEBUG, $type = self::FILE, $file = '', $extra = '') {
        if (empty($file)) {
            $file = date("y_m_d") . ".log";
        }
        $file = sprintf("%s/%s", self::_getLogPath(), $file);
        $dir = dirname($file);
        is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0777));
        $now = date(self::$format);
        $logid = Utils::getLogId();
        if ($level == self::DEBUG) {
            error_log("{$now} \033[34m {$level}: \033[0m {$logid} {$message}\r\n", $type, $file, $extra);
        } else if ($level == self::ERROR) {
            error_log("{$now} \033[31m {$level}: \033[0m  {$logid}  {$message}\r\n", $type, $file, $extra);
        } else {
            error_log("{$now} \033[37m {$level}: \033[0m  {$logid}  {$message}\r\n", $type, $file, $extra);
        }
    }

    /**
     * 错误日志打印
     * @param $message
     */
    static function error($message) {
        $file = 'error_' . date('y_m_d') . ".log";
        self::write($message, self::ERROR, self::FILE, $file);
    }

    /**
     * sql日志
     * @param $message
     * @param string $level
     */
    static function sql($message, $level = self::SQL) {
        $destination = "sql_" . date('y_m_d') . ".log";
        self::write($message, $level, self::FILE, $destination);
    }


    /**
     * http日志
     * @param $message
     * @param string $level
     */
    static function http($message, $level = self::DEBUG) {
        $destination = "http_" . date('y_m_d') . ".log";
        self::write($message, $level, self::FILE, $destination);
    }

    /**
     * 获取log日志输出目录
     * @return null
     */
    private static function _getLogPath() {
        $path = config('base.log.path');
        if (empty($path)) {
            $path = SYS_PATH . '/_logs';
        }
        return $path;
    }


}