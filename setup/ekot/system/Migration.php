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
/**
 * Migration
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 *
 */
abstract class Migration {

    /**
     * adapter
     *
     * @var AbstractAdapter
     */
    protected $_adapter;

    /**
     * Constructor
     * 
     * @param AbstractAdapter $adapter 
     */
    public function __construct($adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * run
     *
     * @return bool
     */
    public function run() {
        $connection = $this->_adapter->getConnection();
        try {
            $sqls = $this->_adapter->toSql();
            foreach ($sqls as $sql) {
                $stmt = $connection->prepare($sql);
                $ret = $stmt->execute();
                if ($ret === false) {
                    $errorInfo = $stmt->errorInfo();
                    throw new PDOException($errorInfo[2]);
                }
            }
            $this->_adapter->clearSql();
            return true;
        } catch (PDOException $e) {
            echo $e->getCode() . ':' . $e->getMessage();
        }
    }
    abstract public function up();
    
    abstract public function down();
}