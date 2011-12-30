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
require_once 'CommentTagGenerator.php';
require_once 'CommentParamGenerator.php';
require_once 'CommentReturnGenerator.php';
/**
 * CommentGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class CommentGenerator extends AbstractGenerator {

    /**
     * name
     * @var string
     */
    private $_name;
    /**
     * description
     * @var string
     */
    private $_description;
    /**
     * tags
     * @var array
     */
    private $_tags = array();
    /**
     * params
     * @var array
     */
    private $_params = array();
    /**
     * return
     * @var CommentReturnGenerator
     */
    private $_return = null;

    /**
     * constructor
     * @param <type> $name
     * @param <type> $description
     */
    public function __construct($name = null, $description = null) {
        if ($name !== null) $this->_name = $name;
        if ($description !== null) $this->_description = $description;
    }

    /**
     * generate
     * @param  bool $indent
     * @return string
     */
    public function generate($indent = false) {
        $out .= self::INDENT .  '/**' . self::LINE_FEED;
        if ($this->_name !== null) {
            $out .= self::INDENT . ' * ' . $this->_name . self::LINE_FEED;
        }
        if ($this->_description !== null) {
            $out .= self::INDENT . ' * ' . $this->_description . self::LINE_FEED;
        }
        foreach ($this->_tags as $tag) {
            $out .= $tag->generate();
        }
        foreach ($this->_params as $param) {
            $out .= $param->generate();
        }
        if ($this->_return !== null) $out .= $this->_return->generate();
        $out .= self::INDENT . ' */' . self::LINE_FEED;
        if ($indent === true) $out = str_replace(self::INDENT, '', $out);
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
     * @return CommentGenerator
     */
    public function setName($name) {
        $this->_name = $name;
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
     * @return CommentGenerator
     */
    public function setDescription($description) {
        $this->_description = $description;
        return $this;
    }

    /**
     * getTags
     * @return array
     */
    public function getTags() {
        return $this->_tags;
    }

    /**
     * setTags
     * @param  array $tags
     * @return CommentGenerator
     */
    public function setTags(array $tags) {
        $this->_tags = $tags;
        return $this;
    }

    /**
     * addTag
     * @param  CommentTagGenerator $param
     * @return CommentGenerator
     */
    public function addTag(CommentTagGenerator $tag) {
        $this->_tags[] = $tag;
        return $this;
    }

    /**
     * getParams
     * @return array
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * setParams
     * @param  array $params
     * @return CommentGenerator
     */
    public function setParams(array $params) {
        $this->_params = $params;
        return $this;
    }

    /**
     * addParam
     * @param  CommentParamGenerator $param
     * @return CommentGenerator
     */
    public function addParam(CommentParamGenerator $param) {
        $this->_params[] = $param;
        return $this;
    }

    /**
     * getReturn
     * @return CommentReturnGenerator
     */
    public function getReturn() {
        return $this->_return;
    }

    /**
     * setReturn
     * @param  CommentReturnGenerator $return
     * @return CommentGenerator
     */
    public function setReturn(CommentReturnGenerator $return) {
        $this->_return = $return;
        return $this;
    }
}