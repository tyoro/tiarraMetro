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
 * CommentTagGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class CommentTagGenerator extends AbstractGenerator {

    /**
     * tag
     * @var string
     */
    private $_tag;
    /**
     * description
     * @var string
     */
    private $_description;

    /**
     * constractor
     * @param string $tag
     * @param string $description
     */
    public function __construct($tag = null, $description = null) {
        if ($tag !== null) $this->_tag = $tag;
        if ($description !== null) $this->_description = $description;
    }

    /**
     * generate
     * @return string
     */
    public function generate() {
        $out .= self::INDENT . ' * @';
        if ($this->_tag !== null) $out .= $this->_tag;
        if ($this->_description !== null) $out .= ' ' . $this->_description;
        $out .= self::LINE_FEED;
        return $out;
    }

    /**
     * getTag
     * @return string
     */
    public function getTag() {
        return $this->_tag;
    }

    /**
     * setTag
     * @param  string $tag
     * @return CommentTagGenerator
     */
    public function setTag($tag) {
        $this->_tag = $tag;
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
     * @return CommentTagGenerator
     */
    public function setDescription($description) {
        $this->_description = $description;
        return $this;
    }
}