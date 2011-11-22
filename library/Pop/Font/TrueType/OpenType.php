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
 * @package    Pop_Font
 * @author     Nick Sagona, III <nick@moc10media.com>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * Pop_Font_TrueType_OpenType
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <nick@moc10media.com>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9 beta
 */

class Pop_Font_TrueType_OpenType extends Pop_Font_TrueType
{

    /**
     * Constructor
     *
     * Instantiate a OpenType font file object based on a pre-existing font file on disk.
     *
     * @param  string $font
     * @return void
     */
    public function __construct($font)
    {
        parent::__construct($font);
    }

    /**
     * Method to parse the required tables of the OpenType font file.
     *
     * @return void
     */
    protected function _parseRequiredTables()
    {
        // OS/2
        if (isset($this->tableInfo['OS/2'])) {
            $this->tables['OS/2'] = new Pop_Font_TrueType_Table_Os2($this);

            $this->flags->isSerif = $this->tables['OS/2']->flags->isSerif;
            $this->flags->isScript = $this->tables['OS/2']->flags->isScript;
            $this->flags->isSymbolic = $this->tables['OS/2']->flags->isSymbolic;
            $this->flags->isNonSymbolic = $this->tables['OS/2']->flags->isNonSymbolic;
            $this->capHeight = $this->tables['OS/2']->capHeight;
        }
    }

}
