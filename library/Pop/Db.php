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
 * @package    Pop_Db
 * @author     Nick Sagona, III <nick@moc10media.com>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * Pop_Db
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <nick@moc10media.com>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9 beta
 */

class Pop_Db
{

    /**
     * SQL object
     * @var Pop_Db_Sql
     */
    public $sql = null;

    /**
     * Default database adapter object
     * @var mixed
     */
    public $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the database connection object.
     *
     * @param  string $type
     * @param  array  $options
     * @param  string $prefix
     * @throws Exception
     * @return void
     */
    public function __construct($type, array $options, $prefix = 'Pop_Db_Adapter_')
    {
        $class = $prefix . ucfirst(strtolower($type));

        if (!class_exists($class)) {
            throw new Exception(Pop_Locale::load()->__('Error: That database adapter class does not exist.'));
        } else {
            $this->sql = new Pop_Db_Sql();
            $this->adapter = new $class($options);
        }
    }

    /**
     * Determine whether or not an instance of the DB object exists already,
     * and instantiate the object if it doesn't exist.
     *
     * @param  string $type
     * @param  array  $options
     * @return Pop_Db
     */
    public static function factory($type, array $options)
    {
        return new self($type, $options);
    }

    /**
     * Get the database adapter type.
     *
     * @return string
     */
    public function getAdapterType()
    {
        $type = null;

        $class = get_class($this->adapter);

        if (stripos($class, 'Pdo') !== false) {
            $type = 'Pdo_' . ucfirst($this->adapter->getDbtype());
        } else {
            $type = ucfirst(str_replace('Pop_Db_Adapter_', '', $class));
        }

        return $type;
    }

}
