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
require_once 'AbstractAdapter.php';
/**
 * MySQLAdapter
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class MySQLAdapter extends AbstractAdapter {

    /**
     * column types
     * 
     * @var array
     */
    private static $_types = array(
        'primary_key' => 'int(11) DEFAULT NULL auto_increment PRIMARY KEY ',
        'string'      => 'varchar',
        'text'        => 'text ',
        'integer'     => 'int',
        'float'       => 'float ',
        'decimal'     => 'decimal',
        'datetime'    => 'datetime ',
        'timestamp'   => 'datetime ',
        'time'        => 'datetime ',
        'date'        => 'date ',
        'binary'      => 'blob ',
        'boolean'     => 'tinyint(1) '
    );
    private static $_limits = array(
        'string'      => 255,
        'integer'     => 11
    );
    /**
     * createTable
     *
     * @param  string      $name
     * @param  TableOption $op
     * @return AbstractAdapter
     */
    public function createTable($name, $op = null) {
        if ($op === null) $op = TableOption::instance();
        $str = "CREATE TABLE `$name` (\n";
        if ($op->getId() !== false) {
            $str .= "`id` int(11) NOT NULL auto_increment";
        }
        if ($op->getTimestamp() !== false) {
            $str .= ",\n`updated_at` datetime default NULL";
            $str .= ",\n`created_at` datetime default NULL";
        }
        if ($op->getId() !== false) {
            $str .= ", \nPRIMARY KEY (`id`)";
        }
        $str .= "\n) ENGINE=" . $op->getEngine() . " DEFAULT CHARSET=" . $op->getCharset() . ";";
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * dropTable
     *
     * @param  string $name
     * @return AbstractAdapter
     */
    public function dropTable($name) {
        $str = "DROP TABLE IF EXISTS `$name`;";
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * renameTable
     *
     * @param  string $oldName
     * @param  string $newName
     * @return AbstractAdapter
     */
    public function renameTable($oldName, $newName) {
        $str = "ALTER TABLE `$oldName` RENAME `$newName`;";
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * addColumn
     *
     * @param  string $tableName
     * @param  string $name
     * @param  string $type
     * @param  ColumnOption $op
     * @return AbstractAdapter
     */
    public function addColumn($tableName, $name, $type, $op = null) {
        if ($op === null) $op = ColumnOption::instance();
        $str = "ALTER TABLE `$tableName` ADD COLUMN `$name` ";
        $str .= self::$_types[$type];
        switch ($type) {
            case 'string':
            case 'integer':
               if ($op->getlimit() === 0) {
                   $str .= '(' .self::$_limits[$type] . ') ';
               } else {
                   $str .= '(' . $op->getLimit() .') ';
               }
               break;
            case 'decimal':
                $str .= '(' . $op->getPrecision() . ',' . $op->getScale() . ') ';
            default:
                break;
        }
        if ($op->getDefault() !== null) {
            $str .= "NOT NULL DEFAULT '" . $op->getDefault() . "'";
        }
        if ($op->getDefault() === null && $op->isNull() === false) {
            $str .= "NOT NULL";
        }
        $str .= ';';
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * renameColumn
     *
     * @param  string $tableName
     * @param  string $oldName
     * @param  string $newName
     * @return AbstractAdapter
     */
    public function renameColumn($tableName, $oldName, $newName) {
        $row = $this->getColumn($tableName, $oldName);
        $str = "ALTER TABLE `$tableName` CHANGE `$oldName` `$newName` " . $row['Type'];
        if ($row['Null'] === 'NO') $str .= ' NOT NULL';
        if ($row['Default'] !== null) $str .= ' DEFAULT ' . $row['Default'];
        $str .= $row['Extra'] . ';';
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * changeColumn
     *
     * @param  string $tableName
     * @param  string $name
     * @param  string $type
     * @param  ColumnOption $op
     * @return AbstractAdapter
     */
    public function changeColumn($tableName, $name, $type, $op = null) {
        if ($op === null) $op = ColumnOption::instance();
        $str = "ALTER TABLE `$tableName` MODIFY `$name` ";
        $str .= self::$_types[$type];
        switch ($type) {
            case 'string':
            case 'integer':
               if ($op->getlimit() === 0) {
                   $str .= '(' .self::$_limits[$type] . ') ';
               } else {
                   $str .= '(' . $op->getLimit() .') ';
               }
               break;
            case 'decimal':
                $str .= '(' . $op->getPrecision() . ',' . $op->getScale() . ') ';
            default:
                break;
        }
        if ($op->getDefault() !== null) {
            $str .= "NOT NULL DEFAULT '" . $op->getDefault() . "'";
        }
        if ($op->getDefault() === null && $op->isNull() === false) {
            $str .= "NOT NULL";
        }
        $str .= ';';
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * removeColumn
     *
     * @param  string $tableName
     * @param  string $name
     * @return AbstractAdapter
     */
    public function removeColumn($tableName, $name) {
        $str = "ALTER TABLE `$tableName` DROP `$name`;";
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * addIndex
     *
     * @param  string $tableName
     * @param  string $name
     * @return AbstractAdapter
     */
    public function addIndex($tableName, $name) {
        $str = "ALTER TABLE `$tableName` ADD INDEX (`$name`);";
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * removeIndex
     *
     * @param  string $tableName
     * @param  string $name
     * @return AbstractAdapter
     */
    public function removeIndex($tableName, $name) {
        $str = "DROP INDEX `$name` ON `$tableName`;";
        $this->_sqls[] = $str;
        return $this;
    }

    /**
     * sql
     *
     * @param  string $sql
     * @return AbstractAdapter
     */
    public function sql($sql) {
        $this->_sqls[] = $sql;
        return $this;
    }

    /**
     * getColumn
     *
     * @param  string $tableName
     * @param  string $columnName
     * @return array|false
     */
    private function getColumn($tableName, $columnName) {
        $str = "SHOW COLUMNS FROM `$tableName`;";
        $stmt = $this->_connection->query($str);
        foreach ($stmt as $row) {
            if ($row['Field'] === $columnName) {
                return $row;
            }
        }
        return false;
    }

}