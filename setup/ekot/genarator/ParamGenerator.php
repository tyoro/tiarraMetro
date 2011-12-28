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
require_once 'AbstractGenerator.php';
/**
 * ParamGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class ParamGenerator extends AbstractGenerator {
    /**
     * name
     * @var string
     */
    private $_name;
    /**
     * hint
     * @var string
     */
    private $_hint;
    /**
     * default
     * @var string
     */
    private $_default;
    /**
     * Constructor
     * @param string $name
     * @param string $hint
     * @param string $default
     */
    public function __construct($name = null, $hint = null, $default = null) {
        if ($name !== null)    $this->_name = $name;
        if ($hint !== null)    $this->_hint = $hint;
        if ($default !== null) $this->_default = $default;
    }
    /**
     * generate
     * @return string
     */
    public function generate() {
        if ($this->_name === null) throw new Exception('name is null');
        $out = '';
        if ($this->_hint !== null) $out .= $this->_hint . ' ';
        $out .= '$' . $this->_name;
        if ($this->_default !== null) $out .= ' = ' . $this->_default;
        return $out;
    }
    /**
     * getName
     * @return string
     */
    public function getName() {
        return $this->_name;
    }
    /**
     * setName
     * @param  string $name
     * @return ParamGenerator
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }
    /**
     * getHint
     * @return string
     */
    public function getHint() {
        return $this->_hint;
    }
    /**
     * setHint
     * @param  string $hint
     * @return ParamGenerator
     */
    public function setHint($hint) {
        $this->_hint = $hint;
        return $this;
    }
    /**
     * getDefault
     * @return string
     */
    public function getDefault() {
        return $this->_default;
    }
    /**
     * setDefault
     * @param  string $default
     * @return ParamGenerator
     */
    public function setDefault($default) {
        $this->_default = $default;
        return $this;
    }

}