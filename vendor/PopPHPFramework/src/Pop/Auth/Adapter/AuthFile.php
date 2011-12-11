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
 * @package    Pop_Auth
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;

use Pop\Auth\Auth,
    Pop\File\File,
    Pop\Locale\Locale;

/**
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class AuthFile extends File implements AdapterInterface
{

    /**
     * Field delimiter
     * @var string
     */
    protected $_delimiter = null;

    /**
     * Users
     * @var array
     */
    protected $_users = array();

    /**
     * Constructor
     *
     * Instantiate the AccessFile object
     *
     * @param string $filename
     * @throws Exception
     * @return void
     */
    public function __construct($filename, $delimiter = '|')
    {
        if (!file_exists($filename)) {
            throw new Exception(Locale::factory()->__('The access file does not exist.'));
        }
        parent::__construct($filename, array());
        $this->_delimiter = $delimiter;
        $this->_parse();
    }

    /**
     * Method to authenticate the user
     *
     * @param  string $username
     * @param  string $password
     * @return array
     */
    public function authenticate($username, $password)
    {
        $result = 0;
        $access = null;

        if (!array_key_exists($username, $this->_users)) {
            $result = Auth::USER_NOT_FOUND;
        } else if ($this->_users[$username]['password'] != $password) {
            $result = Auth::PASSWORD_INCORRECT;
        } else if (strtolower($this->_users[$username]['access']) == 'blocked') {
            $result = Auth::USER_IS_BLOCKED;
        } else {
            $access = $this->_users[$username]['access'];
            $result = Auth::USER_IS_VALID;
        }

        return array('result' => $result, 'access' => $access);
    }

    /**
     * Method to parse the source file.
     *
     * @return void
     */
    protected function _parse()
    {
        $entries = explode(PHP_EOL, trim($this->read()));

        foreach ($entries as $entry) {
            $ent = trim($entry);
            $entAry = explode($this->_delimiter , $ent);
            if (isset($entAry[0]) && isset($entAry[1])) {
                $this->_users[$entAry[0]] = array(
                                                'password' => $entAry[1],
                                                'access'   => (isset($entAry[2]) ? $entAry[2] : null)
                                            );
            }
        }
    }
}