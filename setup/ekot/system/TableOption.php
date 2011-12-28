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
 * TableOption
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class TableOption {

    /**
     * charset
     *
     * @var string
     */
    private $_charset = 'utf8';
    /**
     * engine
     *
     * @var string
     */
    private $_engine = 'InnoDB';
    /**
     * timestamp
     *
     * @var bool
     */
    private $_timestamp = true;
    /**
     * id
     *
     * @var boolean
     */
    private $_id = true;

    /**
     * instance
     *
     * @return TableOption
     */
    public static function instance() {
        return new self;
    }

    /**
     * charset
     *
     * @param string $charset
     * @return TableOption
     */
    public function charset($charset) {
        $this->_charset = $charset;
        return $this;
    }

    /**
     * getCharset
     * 
     * @return string
     */
    public function getCharset() {
        return $this->_charset;
    }

    /**
     * engine
     *
     * @param string $engine
     * @return TableOption
     */
    public function engine($engine) {
        $this->_engine = $engine;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getEngine() {
        return $this->_engine;
    }

    /**
     * timestamp
     * 
     * @param bool $stamp
     * @return TableOption
     */
    public function timestamp($stamp) {
        if (is_bool($stamp) === false) throw new Exception("$stamp is not boolean.");
        $this->_timestamp = $stamp;
        return $this;
    }

    /**
     * getTimestamp
     *
     * @return bool
     */
    public function getTimestamp() {
        return $this->_timestamp;
    }

    /**
     * id
     *
     * @param bool $id
     * @return TableOption
     */
    public function id($id) {
        if (is_bool($id) === false) throw new Exception("$id is not boolean.");
        $this->_id = $id;
        return $this;
    }

    /**
     * getId
     * 
     * @return bool
     */
    public function getId() {
        return $this->_id;
    }

}