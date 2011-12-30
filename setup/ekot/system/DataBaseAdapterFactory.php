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
require_once 'MySQLAdapter.php';
/**
 * DataBaseAdapterFactory
 *
 * @package   Ekot\system
 * @author    MATSUO Masaru
 *
 */
class DataBaseAdapterFactory {
    /**
     * getInstance
     * 
     * @return AbstractAdapter
     */
    public static function getInstance() {
        $env = parse_ini_file(dirname(__FILE__) . '/../resource/env.ini');
        $db = parse_ini_file(dirname(__FILE__) . '/../resource/database.ini', true);
        $db = $db[$env['env']];
        switch ($db['adapter']) {
            case 'mysql':
                return new MySQLAdapter($db);
            default:
                $adapter = $db['adapter'];
                throw new Exception("not supported. $adapter");
        }
    }
}