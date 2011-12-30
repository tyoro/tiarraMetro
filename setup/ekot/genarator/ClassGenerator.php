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
require_once 'PropertyGenerator.php';
require_once 'MethodGenerator.php';
require_once 'CommentGenerator.php';
require_once 'CommentTagGenerator.php';
/**
 * ClassGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */
class ClassGenerator extends AbstractGenerator {

    /**
     * comment
     * @var CommentGenerator
     */
    private $_comment;
    /**
     * class name
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
     * require
     * @var array
     */
    private $_require = array();
    /**
     * include
     * @var array
     */
    private $_include = array();
    /**
     * abstract class?
     * @var bool
     */
    private $_isAbstract = false;
    /**
     * extends class
     * @var string
     */
    private $_extends;
    /**
     * implements class
     * @var array
     */
    private $_implements = array();
    /**
     * properties
     * @var array
     */
    private $_properties = array();
    /**
     * methods
     * @var array
     */
    private $_methods = array();

    /**
     *
     * @param string $name
     * @param string $description
     */
    public function __construct($name = null, $description = null) {
        $this->_comment = new CommentGenerator();
        if ($name !== null) {
            $this->_name = $name;
            $this->_comment->setName($name);
        }
        if ($description !== null) {
            $this->_description = $description;
            $this->_comment->setDescription($description);
        }
    }

    /**
     * generate
     * @return string
     */
    public function generate() {
        $out .= '<?php' . self::LINE_FEED;
        $out .= $this->_comment->generate(true);
        foreach ($this->_require as $req) {
            $out .= $req . self::LINE_FEED;
        }
        foreach ($this->_include as $inc) {
            $out .= $inc . self::LINE_FEED;
        }
        $out .= self::LINE_FEED;
        if ($this->_isAbstract === true) $out .= 'abstract ';
        $out .= 'class ' . $this->_name;
        if ($this->_extends !== null) $out .= ' extends ' . $this->_extends;
        if (count($this->_implements) !== 0) {
            $out .= ' implements ';
            foreach ($this->_implements as $impl) {
                $out .= $impl . ', ';
            }
            $out = substr($out, 0, strlen($out) - 2);
        }
        $out .= ' {' . self::LINE_FEED;
        foreach ($this->_properties as $prop) {
            $out .= $prop->generate();
        }
        foreach ($this->_methods as $method) {
            $out .= $method->generate();
        }
        $out .= '}';
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
     * @return ClassGenerator
     */
    public function setComment(CommentGenerator $comment) {
        $this->_comment = $comment;
        return $this;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * setName
     * @param  string $name
     * @return ClassGenerator
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
     * @return ClassGenerator
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
     * @return ClassGenerator
     */
    public function setTags(array $tags) {
        $this->_tags = $tags;
        return $this;
    }

    /**
     * addTag
     * @param  string $name
     * @param  string $description
     * @return ClassGenerator
     */
    public function addTag($name = null, $description = null) {
        $tag = new CommentTagGenerator($name, $description);
        $this->_comment->addTag($tag);
        return $this;
    }

    /**
     * getRequire
     * @return array
     */
    public function getRequire() {
        return $this->_require;
    }

    /**
     * setRequire
     * @param  array $require
     * @return ClassGenerator
     */
    public function setRequire(array $require) {
        $this->_require = $require;
        return $this;
    }

    /**
     * addRequire
     * @param  string $require
     * @param  bool $once
     * @return ClassGenerator
     */
    public function addRequire($require, $once = true) {
        $out = 'require';
        if ($once === true) {
            $out .= '_once ';
        } else {
            $out .= ' ';
        }
        $out .= "'$require';";
        $this->_require[] = $out;
        return $this;
    }

    /**
     * getInclude
     * @return array
     */
    public function getInclude() {
        return $this->_include;
    }

    /**
     * setInclude
     * @param  array $include
     * @return ClassGenerator
     */
    public function setInclude(array $include) {
        $this->_include = $include;
        return $this;
    }

    /**
     * addIncluce
     * @param  string $include
     * @param  bool $once
     * @return ClassGenerator
     */
    public function addInclude($include, $once = true) {
        $out = 'include';
        if ($once === true) {
            $out .= '_once ';
        } else {
            $out .= ' ';
        }
        $out .= "'$include';";
        $this->_include[] = $out;
        return $this;
    }

    /**
     * isAbstract
     * @return bool
     */
    public function isAbstract() {
        return $this->_isAbstract;
    }

    /**
     * setAbstract
     * @param  bool $abstract
     * @return ClassGenerator
     */
    public function setAbstract($abstract) {
        $this->_isAbstract = $abstract;
        return $this;
    }

    /**
     * getExtends
     * @return string
     */
    public function getExtends() {
        return $this->_extends;
    }

    /**
     * setExtends
     * @param  string $extends
     * @return ClassGenerator
     */
    public function setExtends($extends) {
        $this->_extends = $extends;
        return $this;
    }

    /**
     * getImplements
     * @return array
     */
    public function getImplements() {
        return $this->_implements;
    }

    /**
     * setImplements
     * @param  array $implements
     * @return ClassGenerator
     */
    public function setImplements(array $implements) {
        $this->_implements = $implements;
        return $this;
    }

    /**
     * addImplements
     * @param  string $implements
     * @return ClassGenerator
     */
    public function addImplements($implements) {
        $this->_implements[] = $implements;
        return $this;
    }

    /**
     * getProperties
     * @return array
     */
    public function getProperties() {
        return $this->_properties;
    }

    /**
     * setProperties
     * @param  array $properties
     * @return ClassGenerator
     */
    public function setProperties(array $properties) {
        $this->_properties = $properties;
        return $this;
    }

    /**
     * addProperties
     * @param  PropertyGenerator $property
     * @return ClassGenerator
     */
    public function addProperty(PropertyGenerator $property) {
        $this->_properties[] = $property;
        return $this;
    }

    /**
     * getMethods
     * @return array
     */
    public function getMethods() {
        return $this->_methods;
    }

    /**
     * setMethods
     * @param  array $methods
     * @return ClassGenerator
     */
    public function setMethods(array $methods) {
        $this->_methods = $methods;
        return $this;
    }

    /**
     * addMethod
     * @param MethodGenerator $method
     * @return <type>
     */
    public function addMethod(MethodGenerator $method) {
        $this->_methods[] = $method;
        return $this;
    }
}