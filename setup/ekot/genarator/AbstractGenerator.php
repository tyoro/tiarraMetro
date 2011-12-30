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
 * AbstractGenerator
 *
 * @package   Ekot
 * @author    MATSUO Masaru
 */

abstract class AbstractGenerator {
    const LINE_FEED = "\n";
    const INDENT = '    ';

    /**
     * generate
     * @return string
     */
    abstract public function generate();
}