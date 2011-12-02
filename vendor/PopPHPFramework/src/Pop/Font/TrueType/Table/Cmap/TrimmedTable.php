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
namespace Pop\Font\TrueType\Table\Cmap;

/**
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class TrimmedTable
{

    /**
     * Method to parse the Trimmed Table (Format 6) CMAP data
     *
     * @return array
     */
    public static function parseData($data)
    {
        $ary = unpack('nfirstCode/' .
                      'nentryCount', substr($data, 0, 4));

        $ary['glyphId'] = array();

        $bytePos = 4;
        for ($i = 0; $i < $ary['entryCount']; $i++) {
            $ar = unpack('nglyphIndex', substr($data, $bytePos, 2));
            $ary['glyphId'][$i] = $ar['glyphIndex'];
            $bytePos += 2;
        }

        return $ary;
    }

}