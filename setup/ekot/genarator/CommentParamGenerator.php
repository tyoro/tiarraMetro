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
 * CommentParamGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class CommentParamGenerator extends AbstractGenerator {

    /**
     * name
     * @var string
     */
    private $_name;
    /**
     * type
     * @var string
     */
    private $_type;
    /**
     * description
     * @var string
     */
    private $_description;

    /**
     * Constructor
     * @param string $name
     * @param string $type
     * @param string $description
     */
    public function __construct($name = null, $type = null, $description = null) {
        if ($name !== null) $this->_name = $name;
        if ($type !== null) $this->_type = $type;
        if ($description !== null) $this->_description = $description;
    }

    /**
     * generate
     * @return string
     */
    public function generate() {
        $out .= self::INDENT . ' * @param';
        if ($this->_type !== null) $out .= ' ' . $this->_type;
        if ($this->_name !== null) $out .= ' $' . $this->_name;
        if ($this->_description !== null) $out .= ' ' . $this->_description;
        $out .= self::LINE_FEED;
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
     * @return CommentParamGenerator
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

    /**
     * getType
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * setType
     * @param  string $type
     * @return CommentParamGenerator
     */
    public function setType($type) {
        $this->_type = $type;
        return $this;
    }

    /**
     * getDescription
     * @return string
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * setDescription
     * @param  string $description
     * @return CommentParamGenerator
     */
    public function setDescription($description) {
        $this->_description = $description;
        return $this;
    }
}