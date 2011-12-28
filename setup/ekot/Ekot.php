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
require_once 'system/DataBaseAdapterFactory.php';
require_once 'genarator/ClassGenerator.php';
require_once 'system/SchemaInfo.php';
/**
 * Ekot
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class Ekot {

    /**
     * adapter
     *
     * @var AbstractAdapter
     */
    private $_adapter;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_adapter = DataBaseAdapterFactory::getInstance();
    }

    /**
     * generate
     *
     * @param  string $name
     * @return void
     */
    public function generate($name) {
        $name = '_' . date('YmdHis') . '_' . $name;
        $clazz = new ClassGenerator($name);
        $clazz->addRequire('system/Migration.php ')
              ->setExtends('Migration')->addMethod(new MethodGenerator('up', 'public'))
              ->addMethod(new MethodGenerator('down', 'public'));
        file_put_contents('migrate/' . $name . '.php', $clazz->generate());
    }

    /**
     * migrate
     * 
     * @param  string $name
     * @return void
     */
    public function migrate($name = null) {
        $version = null;
        // inisialize
        if ($this->_hasSchemaINfo() === false) {
            $schemaInfo = new SchemaInfo($this->_adapter);
            $schemaInfo->up();
            $schemaInfo->run();
            $version = 0;
        }
        // search migration file
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'migrate' . DIRECTORY_SEPARATOR;
        $phps = glob($dir . '*.php');
        // current version
        if ($version !== 0) {
            $version = $this->_getVersion();
        }
        $count = count($phps);
        if ($name === 'rollback') {
            $php = $phps[$version - 1];
            require_once $php;
            $clazzName = $this->_getClassName($php);
            $clazz = new $clazzName($this->_adapter);
            $clazz->down();
            $clazz->run();
            $this->_versionDown();
        } else {
            for ($i = $version; $i < $count; $i++) {
                $php = $phps[$i];
                require_once $php;
                $basename = basename($php);
                $clazzName = $this->_getClassName($php);
                $clazz = new $clazzName($this->_adapter);
                $clazz->up();
                $clazz->run();
                $this->_versionUp();
            }
        }
    }

    public function dump() {
        // search migration file
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'migrate' . DIRECTORY_SEPARATOR;
        $phps = glob($dir . '*.php');
        $dump = null;
        foreach ($phps as $php) {
            require_once $php;
            $clazzName = $this->_getClassName($php);
            $dump .= '-- ' .$clazzName . "\n";
            $clazz = new $clazzName($this->_adapter);
            $clazz->up();
            $dump .= implode("\n", $this->_adapter->toSql()) . "\n";
            $this->_adapter->clearSql();
        }
        file_put_contents('dump.sql', $dump);
    }

    /**
     * getVersion
     *
     * @return void
     */
    public function version() {
        echo $this->_getVersion();
    }

    /**
     * hasSchemaInfo
     *
     * @return bool
     */
    private function _hasSchemaINfo() {
        $str = "SELECT * FROM  information_schema.tables WHERE table_name = 'schema_info' AND table_schema='test';";
        $connection = $this->_adapter->getConnection();
        $stmt = $connection->prepare($str);
        $ret = $stmt->execute();
        return $stmt->rowCount() === 0 ? false : true;
    }


   /**
     * getVersion
     *
     * @return int|Exception
     */
    protected function _getVersion() {
        $str = "SELECT `version` from `schema_info`;";
        $connection = $this->_adapter->getConnection();
        $stmt = $connection->query($str);
        foreach ($stmt as $row) {
            return (int)$row['version'];
        }
        throw new PDOException('DB Error.');
    }

    /**
     * versionUP
     *
     * @return void
     */
    private function _versionUp() {
        $str = "UPDATE `schema_info` SET `version` =`version`+1;";
        $connection = $this->_adapter->getConnection();
        $stmt = $connection->prepare($str);
        $ret = $stmt->execute();
    }

    /**
     * versionDowon
     *
     * @return void
     */
    private function _versionDown() {
        $str = 'UPDATE `schema_info` SET `version` =`version`-1;';
        $connection = $this->_adapter->getConnection();
        $stmt = $connection->prepare($str);
        $ret = $stmt->execute();
    }

    /**
     * getClassName
     *
     * @param  string $php
     * @return string
     */
    private function _getClassName($php) {
        $basename = basename($php);
        return substr($basename, 0, strpos($basename, '.'));
    }

 }