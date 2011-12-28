<?php
/**
 * Ekot - the PHP Simple Migration
 *
 * PHP version 5
 *
 * @category  Ekot
 * @package   Ekot
 * @link      http://bitbucket.org/localdisk/ekot/
 * @author    MATSUO Masaru
 * @copyright 2010 MATSUO Masaru
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 *
 */
require_once 'TableOption.php';
require_once 'ColumnOption.php';
/**
 * AbstractAdapter
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
abstract class AbstractAdapter {

    /**
     * connection
     *
     * @var PDO
     */
    protected $_connection;

    /**
     * sqls
     * 
     * @var array
     */
    protected $_sqls = array();

    /**
     * Constructor
     */
    public function __construct(array $db) {
        $dsn = $db['adapter'] . ':dbname' . '=' . $db['dbname'] . ';' . 'host=' . $db['host'];
        try {
            $this->_connection = new PDO($dsn, $db['username'], $db['password']);
        } catch (PDOException $ex) {
            throw $ex;
        }
    }

    /**
     * getConnection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->_connection;
    }

    /**
     * toSql
     * 
     * @return string
     */
    public function toSql() {
        return $this->_sqls;
    }

    /**
     * clearSql
     *
     * @return void
     */
    public function clearSql() {
        $this->_sqls = array();
    }

    /**
     * createTable
     * 
     * @param string $name
     * @param TableOption $op
     * @return AbstractAdapter
     */
    abstract public function createTable($name, $op = null);
    
    /**
     * dropTable
     *
     * @param  string $name
     * @return AbstractAdapter
     */
    abstract public function dropTable($name);
    
    /**
     * renameTable
     *
     * @param  string $oldName
     * @param  string $newName
     * @return AbstractAdapter
     */
    abstract public function renameTable($oldName, $newName);
    
    /**
     * renameTable
     *
     * @param  string $oldName
     * @param  string $newName
     * @return AbstractAdapter
     */
    abstract public function addColumn($tableName, $name, $type, $op = null);
    
    /**
     * renameColumn
     *
     * @param  string $tableName
     * @param  string $oldName
     * @param  string $newName
     * @return AbstractAdapter
     */
    abstract public function renameColumn($tableName, $oldName, $newName);
    
    /**
     * renameColumn
     *
     * @param  string $tableName
     * @param  string $oldName
     * @param  string $newName
     * @return AbstractAdapter
     */
    abstract public function changeColumn($tableName, $name, $type, $op = null);
    
    /**
     * removeColumn
     *
     * @param  string $tableName
     * @param  string $name
     * @return AbstractAdapter
     */
    abstract public function removeColumn($tableName, $name);
    
    /**
     * addIndex
     *
     * @param  string $tableName
     * @param  string $name
     * @return AbstractAdapter
     */
    abstract public function addIndex($tableName, $name);

    /**
     * removeIndex
     *
     * @param  string $tableName
     * @param  string $name
     * @return AbstractAdapter
     */
    abstract public function removeIndex($tableName, $name);

    /**
     * sql
     * 
     * @param string $sql 
     * @return AbstractAdapter
     */
    abstract public function  sql($sql);
}