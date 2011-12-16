<?php
/**
 * Net_Socket_Tiarra
 *
 * Using socket of tiarra to send message.
 *
 * PHP version 5
 *
 * The MIT License
 *
 * Copyright (c) 2009 sotarok <sotaro.k /at/ gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, diENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Net/Socket
 * @package   Net_Socket_Tiarra
 * @author    sotarok <sotaro.k /at/ gmail.com> <author@mail.com>
 * @copyright 2009 sotarok <sotaro.k /at/ gmail.com>
 * @license   http://openpear.org/package/Net_Socket_Tiarra
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Net_Socket_Tiarra
 */


require_once 'Net/Socket/Tiarra/Exception.php';

/**
 * Net_Socket_Tiarra
 *
 * @category  Net/Socket
 * @package   Net_Socket_Tiarra
 * @author    sotarok <sotaro.k /at/ gmail.com> <author@mail.com>
 * @copyright 2009 sotarok <sotaro.k /at/ gmail.com>
 * @license   http://openpear.org/package/Net_Socket_Tiarra
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Net_Socket_Tiarra
 */
class Net_Socket_Tiarra
{

    /**
     * Sender name
     */
    const sender = "PHP/Net_IRC_TIarra";

    /**
     * Tiarra Protocol
     */
    const protocol = "TIARRACONTROL/1.0";

    /**
     * Message template
     */
    const request_template = "
NOTIFY System::SendMessage %s\r
Sender: %s\r
Notice: %s\r
Channel: %s\r
Charset: %s\r
Text: %s\r
\r
";

    /**
     * Socket name to connect
     * @var    string
     * @access protected
     */
    protected $_socket_name;

    /**
     * Socket resource
     * @var    resource
     * @access protected
     */
    protected $_socket_resource;

    /**
     * Options
     * @var    array
     * @access protected
     */
    protected $_options;

    /**
     * Default opsions
     * @var    array
     * @access protected
     */
    protected $_options_default = array(
        'charset' => 'UTF-8',
    );

    /**
     * constructor
     *
     * @param  string  $socketname socket name (defined in tiarra.conf)
     * @param  array   $options    options array
     * @access public
     */
    public function __construct($socketname, Array $options = array())
    {
        $this->setOptions($this->_options_default);
        $this->setOptions($options);

        $this->_socket_name = $socketname;
    }

    /**
     * setOptions
     *
     * @param  array  $options
     * @return object Return
     * @access public
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $key => $value) {
            $this->_setOption($key, $value);
        }
        return $this;
    }

    /**
     * _setOption
     *
     * @param  unknown   $key
     * @param  unknown   $value
     * @return object    Return
     * @access protected
     */
    protected function _setOption($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * getOption
     *
     * @param  string  $key
     * @return array   Return
     * @access public
     */
    public function getOption($key)
    {
        return $this->_options[$key];
    }

    /**
     * message
     *
     * @param  string                      $channel
     * @param  string                      $text
     * @param  boolean                     $use_notice
     * @return void
     * @access public
     * @throws Net_Socket_Tiarra_Exception Exception
     */
    public function message($channel, $text, $use_notice = false)
    {
        foreach ($this->strSplit($text, 430) as $k => $t) {
            $this->_socket_resource = fsockopen("unix:///tmp/tiarra-control/" . $this->_socket_name);

            if (!$this->_socket_resource) {
                throw new Net_Socket_Tiarra_Exception("error: cannot connect tiarra's socket");
            }

            fwrite($this->_socket_resource,
                sprintf(
                    Net_Socket_Tiarra::request_template, // template

                    Net_Socket_Tiarra::protocol,
                    Net_Socket_Tiarra::sender,
                    ($use_notice ? 'yes' : 'no'),
                    $channel,
                    $this->getOption('charset'),
                    $t
                )
            );

            $response = fgets($this->_socket_resource, 128);
            if(!preg_match('@^' . preg_quote(Net_Socket_Tiarra::protocol, '@') . ' 200 OK@', $response)) {
                throw new Net_Socket_Tiarra_Exception("error: " . $response);
            };

            fclose($this->_socket_resource);
        }
    }

    /**
     * noticeMessage
     *
     * @param  string  $channel Parameter
     * @param  string  $text    Parameter
     * @return void
     * @access public
     */
    public function noticeMessage($channel, $text)
    {
        $this->message($channel, $text, true);
    }

    /**
     * strSplit
     *
     * @param  string  $text    Parameter
     * @param  int     $count   Parameter
     * @return array
     */
    public function strSplit($text, $count)
    {
        $result = array();
        $start = 0;
        while(1) {
            $substr = mb_substr($text, $start, $count, $this->getOption('charset'));
            $result[] = $substr;
            if (mb_strlen($substr, $this->getOption('charset')) < $count) {
                break;
            }
            $start += $count;
        }
        return $result;
    }
}

