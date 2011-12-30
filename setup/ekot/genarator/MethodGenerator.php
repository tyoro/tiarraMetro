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
require_once 'CommentGenerator.php';
require_once 'CommentParamGenerator.php';
require_once 'ParamGenerator.php';
/**
 * MethodGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class MethodGenerator extends AbstractGenerator {

    /**
     * comment
     * @var CommentGenerator
     */
    private $_comment;
    /**
     * name
     * @var string
     */
    private $_name;
    /**
     * scope
     * @var string
     */
    private $_scope;
    /**
     * isStatic
     * @var bool
     */
    private $_isStatic;
    /**
     * body
     * @var mixed
     */
    private $_body;
    /**
     * params
     * @var array
     */
    private $_params = array();
    /**
     * description
     * @var string
     */
    private $_description;

    /**
     * Constructor
     * @param string $name
     * @param string $scope
     * @param bool   $static
     * @param string $body
     * @param array  $params
     */
    public function __construct($name = null, $scope = null, $static = false, $body = null, array $params = array()) {
        $this->_comment = new CommentGenerator();
        if ($name !== null) {
            $this->_comment->setName($name);
            $this->_name = $name;
        }
        if ($scope !== null) $this->_scope = $scope;
        if (is_bool($static)) $this->_isStatic = $static;
        if ($body !== null)  $this->_body = $body;
        if (count($params) !== 0) $this->_params = $params;
    }

    /**
     * generate
     * @return string
     */
    public function generate() {
        if ($this->_name === null || $this->_scope === null) {
            throw new Exception('name or scope is null.');
        }
        $out .= $this->_comment->generate();
        $out .= self::INDENT . $this->_scope;
        if ($this->_isStatic === true) $out .= ' static';
        $out .= ' function ' . $this->_name . '(';
        foreach ($this->_params as $param) {
            $out .= $param->generate() . ', ';
        }
        if (count($this->_params) !== 0) $out = substr($out, 0, strlen($out) - 2);
        $out .= ') {' . self::LINE_FEED . self::INDENT . self::INDENT;
        if ($this->_body !== null) $out .= $this->_body;
        $out .= self::LINE_FEED;
        $out .= self::INDENT . '}' . self::LINE_FEED;
        return $out;
    }

    /**
     * getComment
     * @return CommentGenerator
     */
    public function getComment() {
        return $this->_comment;
    }

    /**
     * setComment
     * @param  CommentGenerator $comment
     * @return MethodGenerator
     */
    public function setComment(CommentGenerator $comment) {
        $this->_comment = $comment;
        return $this;
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
     * @return MethodGenerator
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

    /**
     * getScope
     * @return string
     */
    public function getScope() {
        return $this->_scope;
    }

    /**
     * setScope
     * @param  string $scope
     * @return MethodGenerator
     */
    public function setScope($scope) {
        $this->_scope = $scope;
        return $this;
    }

    /**
     * isStatic
     * @return bool
     */
    public function isStatic() {
        return $this->_isStatic;
    }

    /**
     * setStatic
     * @param  bool $static
     * @return MethodGenerator
     */
    public function setStatic($static) {
        if (!is_bool($static)) throw new Exception('arg is not bool.');
        $this->_isStatic = $static;
        return $this;
    }

    /**
     * getBody
     * @return string
     */
    public function getBody() {
        return $this->_body;
    }

    /**
     * setBody
     * @param  string $body
     * @return MethodGenerator
     */
    public function setBody($body) {
        $this->_body = $body;
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
     * @return MethodGenerator
     */
    public function setParams(array $params) {
        $this->_params = $params;
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
     * @return MethodGenerator
     */
    public function setDescription($description) {
        $this->_description = $description;
        $this->_comment->setDescription($description);
        return $this;
    }

    /**
     * addParam
     * @param  ParamGenerator $param
     * @return MethodGenerator
     */
    public function addParam(ParamGenerator $param, $description = null) {
        $this->_params[] = $param;
        $this->_comment->addParam(new CommentParamGenerator($param->getName(), $param->getHint(), $description));
        return $this;
    }
}