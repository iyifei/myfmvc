<?php
/**
 * 数据库连接池
 * User: myf
 * Date: 17/2/23
 * Time: 上午6:06
 */

namespace Myf\Libs;


class ConnDao {

    //链接池
    private static $conns = array();

    /**
     * 获取mysql数据库链接，数据库配置文件从config/mysql.ini中配置
     * @param string $dbName 链接名称
     * @return null|\PDO
     */
    public static function getPDO($dbName=''){
        if(empty($dbName)){
            $dbName = config('db.default_db');
        }
        $conn = null;
        if(isset(self::$conns[$dbName])){
            $conn = self::$conns[$dbName];
        }else{
            $conn = self::createPDO($dbName);
        }
        return $conn;
    }

    /**
     * 创建MySQL数据库PDO连接
     * @param $dbName
     * @return \PDO
     * @throws CmsException
     */
    public static function createPDO($dbName){
        $mysql = config('db.database');
        $startTime = Utils::getMillisecond();
        if(isset($mysql[$dbName])){
            $db = $mysql[$dbName];
            $host = $db["host"];
            $port = $db["port"];
            $user = $db["username"];
            $password = $db["password"];
            $database = $db["dbname"];
            $charset = $db["charset"];
            $dsn = sprintf("mysql:host=%s;dbname=%s;port=%d;charset=%s", $host, $database, $port, $charset);
            //$conn = new \pdoProxy($dsn, $user, $password);
            $conn = new \PDO($dsn, $user, $password);
            $conn->id=$dbName;
            $conn->table_prefix=$db['prefix'];
            $conn->database = $database;
            $base = config('base.log');
            $showLog = config('base.log.show_sql');
            if($showLog){
                Log::sql(sprintf("\033[34m create pdo conn dns \033[0m =【%s】,ct=【%sms】",$dsn,(Utils::getMillisecond()-$startTime)));
            }
            return $conn;
        }else{
            CmsException::throwExp(sprintf("[%s]数据库配置文件不存在",$dbName));
        }
    }

}