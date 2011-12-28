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
/**
 * PropertyGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class PropertyGenerator extends AbstractGenerator {

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
    private $_isStatic = false;
    /**
     * isConst
     * @var bool
     */
    private $_isConst = false;
    /**
     * type
     * @var string
     */
    private $_type;
    /**
     * body
     * @var mixed
     */
    private $_body;

    /**
     * Constructor
     * @param $name string
     * @param $type string
     * @param $body string
     */
    public function __construct($name = null, $scope = null, $type = null, $body = null) {
        $this->_comment = new CommentGenerator();
        if ($name !== null) {
            $this->_name = $name;
            $this->_comment->setName($name);
        }
        if ($scope !== null) $this->_scope = $scope;
        if ($type !== null) {
            $this->_type = $type;
            $this->_comment->addTag(new CommentTagGenerator('var', $type));
        }
        if ($body !== null) $this->_body = $body;
    }

    /**
     * generate
     * @return string
     */
    public function generate() {
        if ($this->_name === null)  throw new Exception('name is null');
        if ($this->_scope === null && $this->_isConst === false) {
            throw new Exception('scope is null');
        }
        $out .= $this->_comment->generate();
        $out .= self::INDENT;
        if ($this->_isConst === true) {
            $out .= 'const ' . $this->_name;
        } else {
            if ($this->_isStatic === true) {
                $out .= $this->_scope . ' static ' . ' $' . $this->_name;
            } else {
                $out .= $this->_scope . ' $' . $this->_name;
            }
        }
        if ($this->_body !== null) $out .= ' = ' . $this->_body;
        $out .= ';' . self::LINE_FEED;
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
     * @return PropertyGenerator
     */
    public function setName($name) {
        $this->_name = $name;
        $this->_comment->setName($name);
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
     * @return PropertyGenerator
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
     * @return PropertyGenerator
     */
    public function setStatic($static) {
        if (!is_bool($static)) throw new Exception('arg type is not boolean');
        $this->_isStatic = $static;
        return $this;
    }

    /**
     * isConst
     * @return bool
     */
    public function isConst() {
        return $this->_isConst;
    }

    /**
     * isConst
     * @param  bool $const
     * @return PropertyGenerator
     */
    public function setConst($const) {
        if (!is_bool($const)) throw new Exception('arg type is not boolean');
        $this->_isConst = $const;
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
     * @return PropertyGenerator
     */
    public function setType($type) {
        $this->_type = $type;
        $this->_comment->addTag(new CommentGenerator('var', $type));
        return $this;
    }

    /**
     * getBody
     * @return mixed
     */
    public function getBody() {
        return $this->_body;
    }

    /**
     * setBody
     * @param  mixed $body
     * @return PropertyGenerator
     */
    public function setBody($body) {
        $this->_body = $body;
        return $this;
    }
}