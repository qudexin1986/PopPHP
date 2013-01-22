<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2013 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Feed\Type\Atom;

/**
 * YouTube Atom feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2013 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.2.0
 */
class Youtube extends \Pop\Feed\Type\Atom
{

    /**
     * Feed URLs
     * @var array
     */
    public static $urls = array(
        'name' => 'http://gdata.youtube.com/feeds/base/users/[{name}]/uploads?v=2',
        'id'   => 'http://gdata.youtube.com/feeds/api/playlists/[{id}]?v=2'
    );

    /**
     * Method to get Twitter RSS URL
     *
     * @param  string $key
     * @param  string $value
     * @return string
     */
    public static function url($key, $value)
    {
        $url = null;

        if (isset(self::$urls[$key])) {
            $url = str_replace('[{' . $key . '}]', $value, self::$urls[$key]);
        }

        return $url;
    }

    /**
     * Method to parse an XML YouTube Atom feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        $items = $this->feed->items;
        foreach ($items as $key => $item) {
            $id = substr($item['link'], (strpos($item['link'], 'v=') + 2));
            if (strpos($id, '&') !== false) {
                $id = substr($id, 0, strpos($id, '&'));
            }
            $items[$key]['id'] = $id;
            $youtube = \Pop\Http\Response::parse('http://gdata.youtube.com/feeds/api/videos/' . $id . '?v=2&alt=json');
            if (!$youtube->isError()) {
                $info = json_decode($youtube->getBody(), true);
                $items[$key]['views'] = $info['entry']['yt$statistics']['viewCount'];
                $items[$key]['likes'] = $info['entry']['yt$rating']['numLikes'];
                $items[$key]['duration'] = $info['entry']['media$group']['yt$duration']['seconds'];
                $items[$key]['image_thumb']  = 'http://i.ytimg.com/vi/' . $id . '/default.jpg';
                $items[$key]['image_medium'] = 'http://i.ytimg.com/vi/' . $id . '/mqdefault.jpg';
                $items[$key]['image_large']  = 'http://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
                foreach ($info as $k => $v) {
                    if ($v != '') {
                        $items[$key][$k] = $v;
                    }
                }
            }
        }

        $this->feed->items = $items;
    }

}