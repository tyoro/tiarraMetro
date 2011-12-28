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
require_once 'Migration.php';
/**
 * SchemaInfo
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class SchemaInfo extends Migration {
    public function up() {
        $this->_adapter->createTable('schema_info', TableOption::instance()->timestamp(false))
             ->addColumn('schema_info', 'version', 'integer')
             ->removeColumn('schema_info', 'id');
        $str = "INSERT INTO `schema_info` (version) VALUES(0);";
        $this->_adapter->sql($str);
    }
    public function down() {
        $this->dropTable('schema_info');
    }

}