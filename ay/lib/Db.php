<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2018
 */

namespace ay\lib;

class Db
{

    private static $db;
    private $dbType = 'mysql';
    private $pConnect = true;
    private static $table;
    private $host;
    private $port;
    private $dbName;
    private $user;
    private $pass;
    private $sql = false; //最后一条sql语句
    private $where = '';
    private $order = '';
    private $limit = '';
    private $field = '*';
    private $clear = 0; //状态，0表示查询条件干净，1表示查询条件污染
    private $trans = 0; //事务指令数

    /**
     * 初始化
     * Dc constructor.
     * @param null $conf 数据库配置
     * @throws \Exception
     */
    public function __construct($conf = null)
    {

        class_exists('PDO') or exit("PDO模块不存在");
        if (is_null($conf)) {
            $conf = C();
        }

        $this->dbType = $conf['DB_TYPE'];
        $this->host = $conf['DB_HOST'];
        $this->port = $conf['DB_PORT'];
        $this->user = $conf['DB_USER'];
        $this->pass = $conf['DB_PASS'];
        $this->dbName = $conf['DB_NAME'];

        //连接数据库
        if (is_null(self::$db)) {
            $this->connect();
        }

    }

    /**
     * PDO连接数据库
     * @throws \Exception
     */
    private function connect()
    {
        $dsn = $this->dbType . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbName;
        $options = $this->pConnect ? [\PDO::ATTR_PERSISTENT => true] : [];
        try {
            $dbh = new \PDO($dsn, $this->user, $this->pass, $options);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            throw new \Exception('Connection failed: ' . $e->getMessage());
        }
        $dbh->exec('SET NAMES utf8');
        self::$db = $dbh;
    }

    /**
     * 设置数据表名
     * @param $table
     * @return Db
     * @throws \Exception
     */
    public static function name($table)
    {
        if (empty($table)) {
            exit('不能设置数据表名为空');
        }

        $tableS = C('DB_PRE') . $table;
        self::$table = self::addChar($tableS);
        return new self();
    }

    /**
     * 设置数据表名
     * @param $table
     * @return Db
     * @throws \Exception
     */
    public static function table($table)
    {
        if (empty($table)) {
            exit('不能设置数据表名为空');
        }

        self::$table = self::addChar($table);
        return new self();
    }

    /**
     * 设置当前数据表别名
     * @param $alias
     * @return $this
     * @throws \Exception
     */
    public function alias($alias)
    {
        if (empty($alias) or empty(self::$table)) {
            exit('数据库表名设置别名为空');
        }

        self::$table .= ' AS ' . $alias;
        return $this;
    }

    private static function addChar($value, $num = 0)
    {

        if (strpos($value, "`") === false and $num == 'where' and strstr($value, '.')) {
            $data = $value;
        } else if ($value == '*' or strpos($value, '(') !== false or strpos($value, "`") !== false) {
            $data = $value;
        } else if (strpos($value, "`") === false and $num == 1 and !is_int($value)) {
            $data = "'" . trim($value) . "'";
        } else if (strpos($value, "`") === false and !is_int($value) and $num != 1) {
            $data = "`" . trim($value) . "`";
        } else {
            $data = trim($value);
        }
        return $data;
    }

    /**
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * @param string $sql
     * @return mixed
     */
    public function doQuery($sql = '')
    {
        $this->sql = $sql;
        // echo $sql;
        // exit;
        $result = self::$db->query($this->sql)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 执行语句 针对 INSERT, UPDATE 以及DELETE,exec结果返回受影响的行数
     * @param string $sql
     * @return mixed
     */
    public function doExec($sql = '')
    {
        $this->sql = $sql;
//        echo $sql;exit;
        return self::$db->exec($this->sql);
    }

    /**
     * 执行sql语句，自动判断进行查询或者执行操作
     * @param string $sql SQL指令
     * @return mixed
     */
    public function doSql($sql = '')
    {
        $queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $sql)) {
            return $this->doExec($sql);
        } else {
            return $this->doQuery($sql);
        }
    }

    /**
     * 获取上一次查询的sql
     * @return bool
     */
    public function getLastSql()
    {
        return $this->sql;
    }

    /**
     * 插入方法
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function insert(array $data)
    {
        $data = $this->dataFormat($data);
        if (!$data) {
            exit('插入数据不能为空');
        }

        $sql = 'INSERT INTO ' . self::$table . '(' . implode(',', array_keys($data)) . ') VALUES(' . implode(',', array_values($data)) . ')';
        return $this->doExec($sql);
    }

    /**
     * 批量插入方法
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function insertAll(array $data)
    {

        $value = '(';
        foreach ($data as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $value .= "'" . $v1 . "',";
            }
            $value = rtrim($value, ',');
            $value .= '), (';
        }
        $value = rtrim($value, ', (');

        $sql = 'INSERT INTO ' . self::$table . '(' . implode(',', array_keys($data[0])) . ') VALUES ' . $value;
        return $this->doExec($sql);
    }

    /**
     * 获取最后一次插入时的ID
     * @return mixed
     */
    public function getLastInsId()
    {
        $sql = 'SELECT LAST_INSERT_ID()';
        $res = $this->doQuery($sql);
        return $res;
    }

    /**
     * 删除方法
     * @return mixed
     * @throws \Exception
     */
    public function delete()
    {
        //安全考虑,阻止全表删除
        if (!trim($this->where)) {
            exit('删除条件禁止为空');
        }

        $sql = 'DELETE FROM ' . self::$table . ' ' . $this->where;
        $this->clear = 1;
        $this->clear();
        return $this->doExec($sql);
    }

    /**
     * 更新方法
     * @param array $data
     * @return mixed|void
     * @throws \Exception
     */
    public function update(array $data)
    {
        //安全考虑,阻止全表更新
        if (!trim($this->where)) {
            exit('更新条件禁止为空');
        }

        $data = $this->dataFormat($data);
        if (!$data) {
            return;
        }

        $valArr = array();
        foreach ($data as $k => $v) {
            $valArr[] = $k . '=' . $v;
        }
        $valStr = implode(',', $valArr);
        $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . trim($valStr) . ' ' . trim($this->where);
        return $this->doExec($sql);
    }

    /**
     * 更新语句 字段值加 $number
     * @param array|string $field
     * @param null $number
     * @return mixed|void
     * @throws \Exception
     */
    public function setInc($field, $number = null)
    {
        //安全考虑,阻止全表更新
        if (!trim($this->where)) {
            exit('更新条件禁止为空');
        }

        if (is_array($field)) {
            $data = $this->dataFormat($field);
            if (!$data) {
                return;
            }

            $valArr = array();
            foreach ($data as $k => $v) {
                $valArr[] = $k . '=' . $k . '+' . $v;
            }
            $valStr = implode(',', $valArr);
            $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . trim($valStr) . ' ' . trim($this->where);
        } else if (is_string($field) and is_null($number)) {
            $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . self::addChar($field) . '=' . self::addChar($field) . '+1 ' . trim($this->where);
        } else {
            $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . self::addChar($field) . '=' . self::addChar($field) . '+' . self::addChar($number, 1) . ' ' . trim($this->where);
        }
        return $this->doExec($sql);

    }

    /**
     * 更新语句 字段值减 $number
     * @param array|string $field
     * @param null $number
     * @return mixed|void
     * @throws \Exception
     */
    public function setDec($field, $number = null)
    {
        //安全考虑,阻止全表更新
        if (!trim($this->where)) {
            exit('更新条件禁止为空');
        }

        if (is_array($field)) {
            $data = $this->dataFormat($field);
            if (!$data) {
                return;
            }

            $valArr = array();
            foreach ($data as $k => $v) {
                $valArr[] = $k . '=' . $k . '-' . $v;
            }
            $valStr = implode(',', $valArr);
            $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . trim($valStr) . ' ' . trim($this->where);
        } else if (is_string($field) and is_null($number)) {
            $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . self::addChar($field) . '=' . self::addChar($field) . '-1 ' . trim($this->where);
        } else {
            $sql = 'UPDATE ' . trim(self::$table) . ' SET ' . self::addChar($field) . '=' . self::addChar($field) . '-' . self::addChar($number, 1) . ' ' . trim($this->where);
        }
        return $this->doExec($sql);

    }

    /**
     * 查询函数
     * @return array|boolean 结果集
     */
    public function select()
    {
        $sql = 'SELECT ' . trim($this->field) . ' FROM ' . self::$table . ' ' . trim($this->where) . ' ' . trim($this->order) . ' ' . trim($this->limit);
        $this->clear = 1;
        $this->clear();
        $res = $this->doQuery(trim($sql));
        return isset($res[0]) ? $res : [];
    }

    /**
     * 查询函数
     * @return array|boolean 结果集
     */
    public function find()
    {
        $sql = "SELECT " . trim($this->field) . " FROM " . self::$table . " " . trim($this->where) . " " . trim($this->order) . " " . trim(" LIMIT 1");
        $this->clear = 1;
        $this->clear();
        $res = $this->doQuery(trim($sql));
        return isset($res[0]) ? $res[0] : [];
    }

    /**
     * 设置条件
     * @param $option
     * @param string $where
     * @param string $value
     * @return $this
     */
    public function where($option, $where = '', $value = '')
    {
        if ($this->clear > 0) {
            $this->clear();
        }

        if (strpos($this->where, 'WHERE')) {
            $this->where .= ' AND ';
            $ss = 1;
        } else {
            $this->where = ' WHERE ';
        }
        $qz = '(';
        if (is_string($option)) {

            if (!empty($value)):
                switch ($where) {
                    case ($where == 'neq' or $where == '!='):
                        $tj = '!=';
                        break;
                    case ($where == 'eq' or $where == '='):
                        $tj = '=';
                        break;
                    case ($where == 'like' or $where == '%'):
                        $tj = 'LIKE';
                        break;
                    default:
                        $tj = $where;
                        break;
                }
                $this->where .= $option . $tj . "'" . $value . "'";
            endif;

            if (empty($value)) {
                if (!strpos($where, "'")):
                    $where = "'" . $where . "'";
                endif;

                $this->where .= $option . '=' . $where;
            } else if (empty($where)) {
                $this->where .= $option;
            }
        } else if (is_array($option)) {
            foreach ($option as $k => $v) {
                if (is_array($v)) {
                    if (strstr($this->where, '(')) {
                        $qz = ' ';
                    }

                    if (isset($ss)) {
                        $qz = '(';
                        unset($ss);
                    }
                    $condition = $qz . $this->addChar($v[0], 'where') . ' ' . $v[1] . ' ' . $this->addChar($v[2], 1);
                } else {
                    $this->where .= ' ' . $this->addChar($option[0]) . $option[1] . $option[2];
                    $cc = 1;
                    break;
                }

                $logIc = isset($v[3]) ? ' ' . $v[3] : ' AND';
                $this->where .= isset($mark) ? $logIc . $condition : $condition;
                $mark = 1;

            }
            if (!isset($cc)) {
                $this->where .= ')';
            }

        }

        return $this;
    }

    /**
     * 设置条件
     * @param $option
     * @param string $where
     * @param string $value
     * @return $this
     */
    public function whereOr($option, $where = '', $value = '')
    {
        if ($this->clear > 0) {
            $this->clear();
        }

        if (strpos($this->where, 'WHERE')) {
            $this->where .= ' OR ';
            $ss = 1;
        } else {
            $this->where = ' WHERE ';
        }
        $qz = '(';
        if (is_string($option)) {

            if (!empty($value)):
                switch ($where) {
                    case ($where == 'neq' or $where == '!='):
                        $tj = '!=';
                        break;
                    case ($where == 'eq' or $where == '='):
                        $tj = '=';
                        break;
                    case ($where == 'like' or $where == '%'):
                        $tj = 'LIKE';
                        break;
                    default:
                        $tj = $where;
                        break;
                }
                $this->where .= $option . $tj . "'" . $value . "'";
            endif;

            if (empty($value)) {
                if (!strpos($where, "'")):
                    $where = "'" . $where . "'";
                endif;

                $this->where .= $option . '=' . $where;
            } else if (empty($where)) {
                $this->where .= $option;
            }
        } else if (is_array($option)) {
            foreach ($option as $k => $v) {
                $logIc = ' AND';
                if (is_array($v)) {
                    if (strstr($this->where, '(')) {
                        $qz = ' ';
                    }

                    if (isset($ss)) {
                        $qz = '(';
                        unset($ss);
                    }
                    $condition = $qz . $this->addChar($v[0]) . ' ' . $v[1] . ' ' . $this->addChar($v[2], 1);
                } else {
                    $this->where .= ' ' . $this->addChar($option[0]) . $option[1] . $option[2];
                    $cc = 1;
                    break;
                }
                if (isset($v[3]) and ($v[3] == 'or' or $v[3] == 'OR')) {
                    $logIc = ' OR';
                    $this->where .= isset($mark) ? $logIc . $condition : $condition;
                } else {
                    $this->where .= isset($mark) ? $logIc . $condition : $condition;
                }

                $mark = 1;
            }
            if (!isset($cc)) {
                $this->where .= ')';
            }

        }

        return $this;
    }

    /**
     * 取得数据表的字段信息
     * @return array
     */
    public function tbFields()
    {
        $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="' . str_replace('`', '', self::$table) . '" AND TABLE_SCHEMA="' . $this->dbName . '"';
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach ($result as $key => $value) {
            $ret[$value['COLUMN_NAME']] = 1;
        }
        return $ret;
    }

    /**
     * 过滤并格式化数据表字段
     * @param $data
     * @return array
     */
    public function dataFormat($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $table_column = $this->tbFields();
        $ret = [];
        foreach ($data as $key => $val):
            if (!is_scalar($val)) {
                continue;
            }

            if (array_key_exists($key, $table_column)):
                $key = $this->addChar($key);
                if (is_int($val)) {
                    $val = intval($val);
                } else if (is_float($val)) {
                    $val = floatval($val);
                } else if (preg_match('/^\(\w*(\+|\-|\*|\/)?\w*\)$/i', $val)) {
                    $val = $val;
                } elseif (is_string($val)) {
                    $val = '"' . addslashes($val) . '"';
                }
                $ret[$key] = $val;
            endif;
        endforeach;

        return $ret;
    }

    /**
     * 排序
     * @param $option
     * @param null $sort
     * @return $this
     */
    public function order($option, $sort = null)
    {
        if ($this->clear > 0) {
            $this->clear();
        }

        $this->order = ' ORDER BY ';
        if (!is_null($sort)) {
            $this->order .= $option . ' ' . $sort;
        } else if (is_string($option)) {
            $this->order .= $option;
        } else if (is_array($option)) {
            foreach ($option as $k => $v) {
                $order = $k . " " . $v;
                $this->order .= isset($mark) ? ',' . $order : $order;
                $mark = 1;
            }
        }
        return $this;
    }

    /**
     * 分页
     * @param $page
     * @param null $pageSize
     * @return $this
     */
    public function limit($page, $pageSize = null)
    {
        if ($this->clear > 0) {
            $this->clear();
        }

        if ($pageSize === null) {
            $this->limit = 'LIMIT ' . $page;
        } else {
            $pagEval = intval(($page - 1) * $pageSize);
            $this->limit = 'LIMIT ' . $pagEval . ',' . $pageSize;
        }
        return $this;
    }

    /**
     * 设置字段名
     * @param $field
     * @return $this
     */
    public function field($field)
    {
        if ($this->clear > 0) {
            $this->clear();
        }

//        if (is_string($field)) $field = explode(',', $field);
        //        $nField = array_map(array($this, 'addChar'), $field);
        //        $this->field = implode(',', $nField);
        $this->field = $field;
        return $this;
    }

    /**
     * 查询 左右内全
     * @param $tab
     * @param string $where
     * @param string $join
     * @return $this
     */
    public function join($tab, $where = '', $join = 'INNER')
    {
        if (is_array($tab)) {
            foreach ($tab as $k => $v):
                $table = $k . ' AS ' . $v;
            endforeach;
        } else {
            $table = str_replace(' ', ' AS ', $tab);
        }
        self::$table .= ' ' . $join . ' JOIN ' . $table . ' ON(' . $where . ')';

        return $this;
    }

    /**
     * 计数
     * @return mixed
     */
    public function count()
    {
        $sql = 'SELECT count(' . trim($this->field) . ') FROM ' . self::$table . ' ' . trim($this->where) . " " . trim($this->order);
        $rows = self::$db->query($sql)->fetch();
        return $rows[0];
    }

    /**
     * 统计
     * @return mixed
     */
    public function sum()
    {
        $sql = 'SELECT SUM(' . trim($this->field) . ') FROM ' . self::$table . ' ' . trim($this->where) . " " . trim($this->order);
        $rows = self::$db->query($sql)->fetch();
        return $rows[0];
    }

    /**
     * 清理标记函数
     */
    private function clear()
    {
        $this->where = '';
        $this->order = '';
        $this->limit = '';
        $this->field = '*';
        $this->clear = 0;
    }

    /**
     * 手动清理标记
     * @return $this
     */
    public function clearKey()
    {
        $this->clear();
        return $this;
    }

    /**
     * 启动事务
     */
    public function startTrans()
    {
        //数据rollback 支持
        if ($this->trans == 0) {
            self::$db->beginTransaction();
        }

        $this->trans++;
        return;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @return bool
     */
    public function commit()
    {
        $result = true;
        if ($this->trans > 0) {
            $result = self::$db->commit();
            $this->trans = 0;
        }
        return $result;
    }

    /**
     * 事务回滚
     * @return bool
     */
    public function rollback()
    {
        $result = true;
        if ($this->trans > 0) {
            $result = self::$db->rollback();
            $this->trans = 0;
        }
        return $result;
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        if (!is_null(self::$db)) {
            self::$db = null;
        }

    }

}
