<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Server
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Web;

/**
 * @category   Pop
 * @package    Pop_Server
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Server
{

    /**
     * Server OS
     * @var string
     */
    public $os = null;

    /**
     * Server Distribution
     * @var string
     */
    public $distro = null;

    /**
     * Full Server Software String
     * @var string
     */
    public $software = null;

    /**
     * Server Software
     * @var string
     */
    public $server = null;

    /**
     * Server Software Version
     * @var string
     */
    public $serverVersion = null;

    /**
     * PHP Version
     * @var string
     */
    public $php = null;

    /**
     * Constructor
     *
     * Instantiate the server session object.
     *
     * @return void
     */
    public function __construct()
    {
        $this->software = $_SERVER['SERVER_SOFTWARE'];
        $this->php = PHP_VERSION;
        $this->_detect();
    }

    /**
     * Method to detect properties.
     *
     * @return void
     */
    protected function _detect()
    {
        $matches = array();

        // Set the server OS and distro, if applicable.
        if (preg_match('/(debian|ubuntu|kbuntu|red hat|centos|fedora|suse|knoppix|gentoo|linux)/i', $this->software, $matches) != 0) {
            $this->os = 'Linux';
            $this->distro = $matches[0];
        } else if (preg_match('/(bsd|sun|solaris|unix)/i', $this->software, $matches) != 0) {
            $this->os = 'Unix';
            $this->distro = $matches[0];
        } else if (preg_match('/(win|microsoft)/i', $this->software, $matches) != 0) {
            $this->os = 'Windows';
            $this->distro = 'Microsoft';
        } else if (stripos($this->software, 'mac') !== false) {
            $this->os = 'Mac';
            $this->distro = 'Darwin';
        } else {
            // If unsuccessful, attempt based on path separator.
            if (DIRECTORY_SEPARATOR == '/') {
                $this->os = 'Linux/Unix';
                $this->distro = 'Unknown';
            } else if (DIRECTORY_SEPARATOR == '\\') {
                $this->os = 'Windows';
                $this->distro = 'Microsoft';
            } else {
                $this->os = 'Unknown';
                $this->distro = 'Unknown';
            }
        }

        // Set the server software.
        if (stripos($this->software, 'apache') !== false) {
            $this->server = 'Apache';
        } else if (stripos($this->software, 'iis') !== false) {
            $this->server = 'IIS';
        } else if (stripos($this->software, 'litespeed') !== false) {
            $this->server = 'LiteSpeed';
        } else if (stripos($this->software, 'lighttpd') !== false) {
            $this->server = 'lighttpd';
        } else if (stripos($this->software, 'nginx') !== false) {
            $this->server = 'nginx';
        } else if (stripos($this->software, 'zeus') !== false) {
            $this->server = 'Zeus';
        } else if (stripos($this->software, 'oracle') !== false) {
            $this->server = 'Oracle';
        } else if (stripos($this->software, 'ncsa') !== false) {
            $this->server = 'NCSA';
        }

        // Set the server software version.
        $matches = array();

        preg_match('/\d.\d/', $this->software, $matches);
        if (isset($matches[0])) {
            $this->serverVersion = $matches[0];
        } else {
            $this->serverVersion = 'Unknown';
        }
    }

}