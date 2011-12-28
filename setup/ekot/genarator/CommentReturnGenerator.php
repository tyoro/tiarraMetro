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
 * CommentReturnGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class CommentReturnGenerator extends AbstractGenerator {

    /**
     * type
     * @var string
     */
    private $_type;

    /**
     * Constructor
     * @param string $type 
     */
    public function __construct($type = null) {
        if ($type !== null) $this->_type = $type;
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
     * @return CommentReturnGenerator
     */
    public function setType($type) {
        $this->_type = $type;
        return $this;
    }

    /**
     * generate
     * @return string
     */
    public function generate() {
        $out = self::INDENT . ' * @return ' . $this->_type . self::LINE_FEED;
        return $out;
    }
}