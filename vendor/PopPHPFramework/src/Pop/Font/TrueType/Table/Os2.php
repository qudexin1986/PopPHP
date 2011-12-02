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
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Font\TrueType\Table;

/**
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Os2
{

    /**
     * Font cap height value
     * @var int
     */
    public $capHeight = 0;

    /**
     * Font flags
     * @var ArrayObject
     */
    public $flags = null;

    /**
     * Constructor
     *
     * Instantiate a OTF 'OS/2' table object.
     *
     * @param  Pop_Font $font
     * @return void
     */
    public function __construct($font)
    {
        $this->flags = new \ArrayObject(array('isFixedPitch'  => false,
                                              'isSerif'       => false,
                                              'isSymbolic'    => false,
                                              'isScript'      => false,
                                              'isNonSymbolic' => false,
                                              'isItalic'      => false,
                                              'isAllCap'      => false,
                                              'isSmallCap'    => false,
                                              'isForceBold'   => false), \ArrayObject::ARRAY_AS_PROPS);

        $bytePos = $font->tableInfo['OS/2']->offset + 30;
        $ary = unpack("nfamily_class", $font->read($bytePos, 2));
        $familyClass = ($font->shiftToSigned($ary['family_class']) >> 8);

        if ((($familyClass >= 1) && ($familyClass <= 5)) || ($familyClass == 7)) {
            $this->flags->isSerif = true;
        } else if ($familyClass == 8) {
            $this->flags->isSerif = false;
        }
        if ($familyClass == 10) {
            $this->flags->isScript = true;
        }
        if ($familyClass == 12) {
            $this->flags->isSymbolic = true;
            $this->flags->isNonSymbolic = false;
        } else {
            $this->flags->isSymbolic = false;
            $this->flags->isNonSymbolic = true;
        }

        // Unicode bit-sniffing may not be necessary.
        $bytePos += 3;
        $ary = unpack("NunicodeRange1/" .
                      "NunicodeRange2/" .
                      "NunicodeRange3/" .
                      "NunicodeRange4", $font->read($bytePos, 16));

        if (($ary['unicodeRange1'] == 1) && ($ary['unicodeRange2'] == 0) && ($ary['unicodeRange3'] == 0) && ($ary['unicodeRange4'] == 0)) {
            $this->flags->isSymbolic = false;
            $this->flags->isNonSymbolic = true;
        }

        $bytePos = $font->tableInfo['OS/2']->offset + 76;
        $ary = unpack("ncap/", $font->read($bytePos, 2));
        $this->capHeight = $font->toEmSpace($font->shiftToSigned($ary['cap']));
    }

}