<?php
/**
 * 基础数据库操作类
 * User: myf
 * Date: 17/2/23
 * Time: 上午6:03
 */

namespace Myf\Libs;


/**
 * Class DB
 * @package Myf\Libs
 */
class DB {

    //链接
    private $_conn;
    //db
    static private $_dbs;
    private $_db;
    //查询sql
    private $_sql = '';
    //查询条件
    private $_options = [];
    //当前操作的table
    private $_table = '';
    //数据库
    private $_database;
    //是否日志
    private $_log = false;


    //构造函数
    function __construct($dbName) {
        $this->_conn = ConnDao::getPDO($dbName);
        $this->_database = $this->_conn->database;
        if (config('base.log.show_sql')) {
            $this->_log = true;
        }
    }

    public static function getInstance($dbName) {
        if (!isset(self::$_dbs[$dbName])) {
            self::$_dbs[$dbName] = new self($dbName);
        }
        return self::$_dbs[$dbName];
    }

    /**
     * 设置表
     * @param $tableName
     * @return \Myf\Libs\DB
     */
    public function table($tableName) {
        //需要加上链接配置的prefix
        $this->_table = $this->_conn->table_prefix . $tableName;
        $this->_options['table'] = $this->_table;
        return $this;
    }

    /**
     * 获取表前缀
     * @return mixed
     */
    public function getTablePrefix() {
        return $this->_conn->table_prefix;
    }

    /**
     * 查询一条结果
     * @return Object
     */
    public function findFirst() {
        return $this->findAll(false);
    }

    /**
     * 所有记录中查询
     * @param boolean $all true-返回所有记录，false-返回一条记录
     * @return Object
     */
    public function findAll($all = true) {
        if (!isset($this->_options["field"])) {
            $this->_options["field"] = "*";
        }
        $sql = "SELECT {$this->_options["field"]} FROM `{$this->_table}`";
        if (isset($this->_options["where"])) {
            $sql .= " WHERE " . $this->_options["where"];
        }
        if (isset($this->_options["group"])) {
            $sql .= " GROUP BY  " . $this->_options["group"];
        }
        if (isset($this->_options["order"])) {
            $sql .= " ORDER BY " . $this->_options["order"];
        }
        if (isset($this->_options["limit"])) {
            $sql .= " LIMIT " . $this->_options["limit"];
        }
        if (!isset($this->_options["bindArray"])) {
            $this->_options["bindArray"] = array();
        }
        $this->_sql = $sql;
        $action = "select";
        if ($all) {
            $action = "selectAll";
        }
        return $this->execute($sql, $this->_options["bindArray"], $action);
    }


    /**
     * sql查询，返回一条结果对象
     * @param String $sql sql语句
     * @param Array $bindArray 绑定参数
     * @return Object
     */
    public function findAllSql($sql, $bindArray = array()) {
        $res = $this->execute($sql, $bindArray, "selectAll");
        if ($res) {
            return $res;
        } else {
            return [];
        }
    }

    /**
     * sql查询，返回结果集
     * @param String $sql sql语句
     * @param Array $bindArray 绑定参数
     * @return Array
     */
    public function findFirstSql($sql, $bindArray = array()) {
        $res = $this->execute($sql, $bindArray, "select");
        return $res;
    }

    /**
     * sql查询个数
     * @param String $sql sql语句
     * @param Array $bindArray 绑定参数
     * @return int 个数
     */
    public function countSql($sql, $bindArray = array()) {
        $row = $this->findFirstSql($sql, $bindArray);
        return intval(current($row));
    }

    /**
     * 查询记录个数
     * @return int 数量
     */
    public function count() {
        $sql = "SELECT count(*) AS ROWCOUNT FROM `{$this->_table}` ";
        $bindArray = array();
        if (isset($this->_options["where"])) {
            $sql .= " WHERE " . $this->_options["where"];
            if (isset($this->_options["bindArray"]) && is_array($this->_options["bindArray"])) {
                $bindArray = $this->_options["bindArray"];
            }
        }
        $res = $this->execute($sql, $bindArray, "select");
        return intval($res["ROWCOUNT"]);
    }

    /**
     * 添加数据
     * @param Array $data 需要添加的数据
     * @return int
     */
    public function add($data) {
        if (is_array($data)) {
            $sql = "INSERT INTO `{$this->_table}` ";
            $fields = $values = $bindArray = array();
            foreach ($data as $key => $val) {
                $fields[] = "`{$key}`";
                $values[] = ":" . $key;
                $bindArray[":" . $key] = $val;
            }
            $field = join(',', $fields);
            $value = join(',', $values);
            unset($fields, $values);
            $sql .= "({$field}) VALUES({$value})";
            return $this->execute($sql, $bindArray, "insert");
        } else {
            return 0;
        }
    }

    /**
     * 更新数据
     * @param Array $data 需要更新的数据
     * @param String $where 查询条件
     * @param Array $bindArray 数据对应关系
     * @return int 影响行数
     */
    public function update($data, $where = null, $bindArray = array()) {
        if (is_array($data)) {
            $table = $this->_table;
            $fields = $values = array();
            if (!is_array($bindArray)) {
                $bindArray = array();
            }
            $sql = "UPDATE `{$table}` SET ";
            foreach ($data as $key => $val) {
                $fields[] = "`{$key}`";
                $values[] = "`{$table}`.`{$key}`=:" . $key;
                $bindArray[":" . $key] = $val;
            }
            $value = join(",", $values);
            $sql .= $value;
            if (isset($where)) {
                $sql .= " WHERE {$where} ";
            }
            return $this->execute($sql, $bindArray, "update");
        } else {
            return 0;
        }
    }

    /**
     * 删除记录
     * @return int 影响行数
     */
    public function delete() {
        $table = $this->_table;
        $sql = "DELETE FROM `{$table}`";
        $bindArray = array();
        if (isset($this->_options["where"])) {
            $sql .= " WHERE {$this->_options["where"]}";
            if (isset($this->_options["bindArray"]) && is_array($this->_options["bindArray"])) {
                $bindArray = $this->_options["bindArray"];
            }
        }
        return $this->execute($sql, $bindArray, "delete");
    }

    /**
     * 绑定参数
     * @param Array $bindArray
     * @return $this
     */
    public function bind($bindArray) {
        if (is_array($bindArray)) {
            $this->_options["bindArray"] = $bindArray;
        }
        return $this;
    }

    /**
     * 读取表的主键
     * @return string 主键名称
     */
    public function findPrimaryKey() {
        $dbname = $this->_database;
        $table = $this->_table;
        $sql = "select column_name from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where constraint_name='PRIMARY' AND table_name='{$table}' and table_schema='{$dbname}'";
        $row = $this->findFirstSql($sql);
        if ($row) {
            return $row["column_name"];
        } else {
            return null;
        }
    }

    /**
     * 获取表的字段，返回key为字段名称，value为字段的类型
     * @return array Object
     */
    public function findColumns() {
        $dbname = $this->_database;
        $table = $this->_table;
        $sql = sprintf("select COLUMN_NAME,DATA_TYPE from INFORMATION_SCHEMA.Columns where table_name='%s' and table_schema='%s'", $table, $dbname);
        $rows = $this->findAllSql($sql);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
        }
        return $map;
    }


    /**
     * 查询条件,仅能配合findAll,findFirst,delete,count使用
     * @param string $conditions
     * @param array $bindArray
     * @return $this
     */
    public function where($conditions, $bindArray = []) {
        $this->_options['where'] = $conditions;
        $this->bind($bindArray);
        return $this;
    }

    /**
     * 返回记录数,仅能配合findAll,findFirst使用
     * @param $fields
     * @return $this
     */
    public function field($fields) {
        if (is_string($fields)) {
            $this->_options['field'] = $fields;
        } elseif (is_array($fields)) {
            $this->_options['field'] = join(',', $fields);
        }
        return $this;
    }

    /**
     * 限制返回记录条数,仅能配合findAll,findFirst使用
     * @param int $start 开始记录/返回总记录
     * @param null|int $size
     * @return $this
     */
    public function limit($start, $size = null) {
        if (isset($size)) {
            $this->_options['limit'] = sprintf("%d,%d", $start, $size);
        } else {
            $this->_options['limit'] = $start;
        }
        return $this;
    }

    /**
     * 排序，如：id desc,仅能配合findAll,findFirst使用
     * @param string $order
     * @return $this
     */
    public function order($order) {
        if (is_string($order)) {
            $this->_options['order'] = $order;
        }
        return $this;
    }

    /**
     * 事务开始
     */
    public function begin() {
        $this->_conn->beginTransaction();
        if ($this->_log) {
            Log::sql(sprintf("\033[34m begin transaction \033[0m conn=【%s】", $this->_conn->id));
        }
    }

    /**
     * 事务提交
     */
    public function commit() {
        $this->_conn->commit();
        if ($this->_log) {
            Log::sql(sprintf("\033[34m commit transaction \033[0m conn=【%s】", $this->_conn->id));
        }
    }

    /**
     * 事务回滚
     */
    public function rollBack() {
        $this->_conn->rollBack();
        if ($this->_log) {
            Log::sql(sprintf("\033[31m rollBack transaction \033[0m conn=【%s】", $this->_conn->id));
        }
    }

    /**
     * 判断表是否存在
     * @param $tableName
     * @return bool
     */
    public function isExistTable($tableName) {
        $tables = $this->getTables();
        if (in_array($this->getTablePrefix().$tableName, $tables)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 获取数据库下所有表名
     * @return array
     */
    public function getTables() {
        $rows = $this->findAllSql('SHOW TABLES');
        $tables = [];
        foreach ($rows as $row) {
            foreach ($row as $table) {
                $tables[] = $table;
            }
        }
        return $tables;
    }

    /**
     * 获取表的所有字段
     * @param $table
     * @return array
     */
    public function getFields($table){
        $fields = array();
        $table = $this->getTablePrefix() . $table;
        $data = $this->findAllSql("SHOW COLUMNS FROM $table");
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }

    /**
     * 判断字符是否存在
     * @param $table
     * @param $field
     * @return bool
     */
    public function isExistField($table,$field){
        $fields = $this->getFields($table);
        return in_array($field,$fields);
    }


    /**
     * 执行全局sql
     * @param $sql
     * @param array $bindArray
     * @param null $action
     * @return array|int|mixed|null|string
     * @throws \Exception
     */
    public function execute($sql, $bindArray = array(), $action = null) {
        $sqlStartTime = getMillisecond();
        $stmt = $this->_conn->prepare($sql);
        $showLog = $this->_log;
        if ($showLog) {
            Log::sql(sprintf("start sql=【%s】,bind=【%s】", $sql, json_encode($bindArray)));
        }
        $ok = $stmt->execute($bindArray);
        $res = null;
        switch ($action) {
            case "select":
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);
                break;
            case "selectAll":
                $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                break;
            case "update":
            case "delete":
                $res = $stmt->rowCount();
                break;
            case "insert":
                $res = $this->_conn->lastInsertId();
                break;
            case "count":
                $res = $stmt->rowCount();
                break;
        }
        $this->_options = array();
        $sqlEndTime = getMillisecond();
        if ($showLog) {
            Log::sql(sprintf("execute ct=【%sms】,ec=【%s】,conn=【%s】,sql=【%s】,bind=【%s】", ($sqlEndTime - $sqlStartTime), $ok, $this->_conn->id, $sql, jsonCNEncode($bindArray)), Log::SQL);
        }
        if (!$ok) {
            Log::sql(sprintf("ERROR err=【%s】", jsonCNEncode($stmt->errorInfo())), Log::ERROR);
            MyfException::throwExp(jsonCNEncode($stmt->errorInfo()));
        }
        return $res;
    }


}