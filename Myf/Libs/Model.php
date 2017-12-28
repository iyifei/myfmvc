<?php
/**
 * 数据库操作基类
 * User: myf
 * Date: 2017/8/29
 * Time: 20:31
 */

namespace Myf\Libs;


class Model
{

    private static $_model;
    private $_db;
    private $_tableName;
    private $_dbName;

    //构造函数
    public function __construct() {
        $className = get_class($this);
        //默认连接的数据库配置项
        $dbName = getDefaultDbName();
        //判断子类是否有getDatabaseName方法，如果有，代表要设置新的数据库配置项
        $hasSourceMethod = method_exists($this, "getDatabaseName");
        if ($hasSourceMethod) {
            $dbName = $this->getDatabaseName();
        }
        $tableName = toUnderLineName(getClassFileName($className));
        //去掉未部的_model,把user_model改为user
        $tableNames = explode("_",$tableName);
        $len = count($tableNames);
        if($tableNames[$len-1]=='model'){
            unset($tableNames[$len-1]);
        }
        $tableName = join("_",$tableNames);
        //判断是否有充值表名称
        $hasTableMethod = method_exists($this, 'getTableName');
        if ($hasTableMethod) {
            $tableName = $this->getTableName();
        }
        $this->_tableName = $tableName;
        $this->_dbName = $dbName;
        $db = table($tableName, $dbName);
        $this->_db = $db;
    }

    /**
     * 单例对象
     * @return \Myf\Libs\Model
     */
    public static function getInstance() {
        if (!isset(self::$_model)) {
            $class = get_called_class();
            self::$_model = new $class;
        }
        return self::$_model;
    }

    /**
     * 查询记录
     * @param array ,String $args 查询条件
     * @param Boolean|array|string|int|Double $value 单属性查询值
     * @return Array 记录集
     */
    public function findAll($args = null, $value = false) {
        $this->setOptions($args, $value);
        $res = $this->_db->findAll();
        if (!$res) {
            $res = array();
        }
        return $res;
    }

    /**
     * 查询sql语句
     * @param String $sql
     * @param array $bind
     * @return Object
     */
    public function findAllBySql($sql,$bind=[]){
        return $this->_db->findAllSql($sql,$bind);
    }

    /**
     * 查找一条记录
     * @param Array ,String,Integer $args 检索条件
     * @param Boolean|Object|Array|String|Int|Double $value 单属性查询值
     * @return Array|false 记录对象
     */
    public function findFirst($args = null, $value = false) {
        $this->setOptions($args, $value);
        return $this->_db->findFirst();
    }

    /**
     * sql查询一条记录
     * @param string $sql
     * @param array $bind
     * @return Array
     */
    public function findFirstBySql($sql,$bind=[]){
       return $this->_db->findFirstSql($sql,$bind);
    }

    /**
     * 根据Id查询记录
     * @param int $id
     * @return Object
     */
    public function findById($id) {
        return $this->findFirst('id', $id);
    }

    /**
     * 锁表读取
     * @param $id
     * @return Array
     */
    public function findByIdForUpdate($id){
        $db = database($this->_dbName);
        $sql = sprintf("select * from %s where id=:id for update",$this->_tableName);
        return $db->findFirstSql($sql,['id'=>$id]);
    }

    /**
     * 设置查询条件
     * @param String $conditions
     * @param Array $bind
     * @return \Myf\Libs\Model
     */
    public function where($conditions, $bind = array()) {
        $this->_db->where($conditions);
        $this->_db->bind($bind);
        return $this;
    }

    /**
     * 设置排序
     * @param String $order
     * @return \Myf\Libs\Model
     */
    public function order($order) {
        $this->_db->order($order);
        return $this;
    }

    /**
     * 设置查询字段
     * @param Array ,String $columns
     * @return \Myf\Libs\Model
     */
    public function columns($columns) {
        if (is_array($columns)) {
            $columns = join(",", $columns);
        }
        $this->_db->field($columns);
        return $this;
    }

    /**
     * 设置查询条件
     * @param Array ,String $args 检索条件
     * @param Boolean|Array|String|int $value 单属性查询值
     * @return \Myf\Libs\Model
     */
    private function setOptions($args, $value = false) {
        $this->_db->table($this->_tableName);
        if (gettype($value) != 'boolean') {
            $this->_db->where($args . "=:key");
            $this->_db->bind(array(":key" => $value));
        } else {
            if (is_array($args)) {
                //查询条件
                $conditions = isset($args["conditions"]) ? $args["conditions"] : $args[0];
                $this->_db->where($conditions);
                //查询列
                if (isset($args["columns"])) {
                    $this->_db->field($args["columns"]);
                }
                //绑定数据
                if (isset($args["bind"]) && is_array($args["bind"])) {
                    $this->_db->bind($args["bind"]);
                }
                //排序
                if (isset($args["order"])) {
                    $this->_db->order($args["order"]);
                }
                //截取数据
                if (isset($args["limit"])) {
                    $this->_db->limit($args["limit"]);
                }
                //分组
                if (isset($args["group"])) {
                    $this->_db->group($args["group"]);
                }
            } elseif (is_numeric($args)) {
                $key = $this->_db->findPrimaryKey();
                $this->_db->where($key . "=:key");
                $this->_db->bind(array(":key" => intval($args)));
            } elseif (is_string($args)) {
                $this->_db->where($args);
            }
        }
        return $this;
    }

    /**
     * 保存数据，如果有主键已经赋值则更新
     * @param Array $data 数据对象
     * @return int 最后插入的主键，如果是更新返回影响行数
     */
    public function save($data = null) {
        //执行前置执行函数
        if (method_exists($this, "beforeSave")) {
            $this->beforeSave();
        }
        //判断数据对象是否为空，如果为空，序列号self
        $isInsert = true;
        $rowId = 0;
        //判断如果有主键，代表更新记录
        $key = $this->_db->findPrimaryKey();
        if (array_key_exists($key, $data)) {
            $keyValue = $data[$key];
            unset($data[$key]);
            if (isset($keyValue)) {
                $isInsert = false;
                //主键有值代表更新
                $rowId = $this->update($data, "id=:id", array(":id" => $keyValue));
            }
        }
        if ($isInsert) {
            $rowId = $this->insert($data);
            $this->$key = $rowId;
        }
        //执行后置函数
        if (method_exists($this, "afterSave")) {
            $this->afterSave();
        }
        return $rowId;
    }

    /**
     * 更新记录
     * @param Array $data 数据数组
     * @param String $where 条件
     * @param Array $bindArray 条件对应数组
     * @return int 影响行数
     */
    public function update($data = null, $where = null, $bindArray = array()) {
        //执行前置执行函数
        if (method_exists($this, "beforeUpdate")) {
            $this->beforeUpdate();
        }
        $this->_db->table($this->_tableName);
        $rows = $this->_db->update($data, $where, $bindArray);
        //执行后置函数
        if (method_exists($this, "afterUpdate")) {
            $this->afterUpdate();
        }
        return $rows;
    }

    /**
     * 根据主键id更新data
     * @param $data
     * @param $id
     * @return int
     */
    public function updateById($data, $id) {
        return $this->update($data, "id=:id", array("id" => $id));
    }

    /**
     * 添加数据
     * @param Array $data 添加数据
     * @return int 插入的主键
     */
    public function insert($data = null) {
        //执行前置执行函数
        if (method_exists($this, "beforeInsert")) {
            $this->beforeInsert();
        }
        $this->_db->table($this->_tableName);
        $rowId = $this->_db->add($data);
        //执行后置函数
        if (method_exists($this, "afterInsert")) {
            $this->afterInsert();
        }
        return $rowId;
    }

    /**
     * 添加数据
     * @param null $data
     * @return int
     */
    public function add($data = null) {
        return $this->insert($data);
    }

    /**
     * 批量添加数据
     * @param Array $rowArray
     */
    public function adds($rowArray) {
        $this->_db->begin();
        foreach ($rowArray as $data) {
            $this->add($data);
        }
        $this->_db->commit();
    }

    /**
     * 删除数据
     * @param Array ,String $args
     * @return int 影响行数
     */
    public function delete($args=null) {
        //执行前置执行函数
        if (method_exists($this, "beforeDelete")) {
            $this->beforeDelete();
        }
        if(isset($args)){
            $this->setOptions($args);
        }
        $rows = $this->_db->delete();
        //执行后置函数
        if (method_exists($this, "afterDelete")) {
            $this->afterDelete();
        }
        return $rows;
    }

    /**
     * 根据id删除
     * @param int $id
     * @return int
     */
    public function deleteById($id) {
        $args = array(
            'id=:id',
            'bind' => array("id" => $id)
        );
        return $this->delete($args);
    }

    /**
     * 查询数量
     * @param Array ,String $args
     * @param Object|Boolean $value 单属性查询值
     * @return int
     */
    public function count($args = null, $value = false) {
        $this->setOptions($args, $value);
        return $this->_db->count();
    }

    /**
     * limit读取几个记录
     * @param $start
     * @param $rows
     * @return mixed
     */
    public function limit($start, $rows) {
        $this->_db->limit($start . "," . $rows);
        return $this;
    }


    /**
     * 开启事物
     */
    public function begin(){
        $this->_db->begin();
    }

    /**
     * 提交事物
     */
    public function commit(){
        $this->_db->commit();
    }


    /**
     * 事物回滚
     */
    public function rollBack(){
        $this->_db->rollBack();
    }

}