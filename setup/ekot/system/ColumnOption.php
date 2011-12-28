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
 * ColumnOption
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class ColumnOption {

    /**
     * limit
     *
     * @var int
     */
    private $_limit = 0;
    /**
     * default
     *
     * @var string
     */
    private $_default = null;
    /**
     * null
     *
     * @var bool
     */
    private $_null = true;
    /**
     * precision
     *
     * @var int
     */
    private $_precision = 10;
    /**
     * scale
     *
     * @var int
     */
    private $_scale = 0;

    /**
     * instance
     *
     * @return ColumnOption
     */
    public static function instance() {
        return new self;
    }

    /**
     * limit
     *
     * @param  int $limit
     * @return ColumnOption
     */
    public function limit($limit) {
        if (is_int($limit) === false) throw new Exception("$limit is not integer.");
        $this->_limit = $limit;
        return $this;
    }

    /**
     * getLimit
     * 
     * @return int
     */
    public function getLimit() {
        return $this->_limit;
    }

    /**
     * defaults
     *
     * @param  mixed $default
     * @return ColumnOption
     */
    public function _default($default) {
        $this->_default = $default;
        return $this;
    }

    /**
     * getDefault
     * 
     * @return mixed
     */
    public function getDefault() {
        return $this->_default;
    }

    /**
     * _null
     *
     * @param bool $null
     * @return ColumnOption
     */
    public function _null($null) {
        if (is_bool($null) === false) throw new Exception("$null is not boolean.");
        $this->_null = $null;
        return $this;
    }

    /**
     * getNull
     *
     * @return bool
     */
    public function isNull() {
        return $this->_null;
    }

    /**
     * precision
     *
     * @param  int $precision
     * @return ColumnOption
     */
    public function precision($precision) {
        if (is_int($precision) === false) throw new Exception("$precision is not integer");
        $this->_precision = $precision;
        return $this;
    }

    /**
     * getPrecision
     *
     * @return int
     */
    public function getPrecision() {
        return $this->_precision;
    }

    /**
     * scale
     * 
     * @param  int $scale
     * @return ColumnOption
     */
    public function scale($scale) {
        if (is_int($scale) === false) throw new Exception("$scale is not integer.");
        $this->_scale = $scale;
        return $this;
    }

    /**
     * getScale
     *
     * @return int
     */
    public function getScale() {
        return $this->_scale;
    }

}