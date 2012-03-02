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
 * @package    Pop_Image
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image;

use Pop\Color\Color,
    Pop\Color\ColorInterface,
    Pop\Color\Rgb,
    Pop\Http\Response,
    Pop\Image\AbstractImage,
    Pop\Image\Exception;

/**
 * This is the Imagick class for the Image component.
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Imagick extends AbstractImage
{

    /**
     * Constant for motion blur
     * @var int
     */
    const MOTION_BLUR = 5;

    /**
     * Constant for radial blur
     * @var int
     */
    const RADIAL_BLUR = 6;

    /**
     * Imagick version
     * @var string
     */
    public $version = null;

    /**
     * Imagick version number
     * @var string
     */
    public $versionString = null;

    /**
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = array(
        'afm'   => 'application/x-font-afm',
        'ai'    => 'application/postscript',
        'avi'   => 'video/x-msvideo',
        'bmp'   => 'image/x-ms-bmp',
        'eps'   => 'application/octet-stream',
        'gif'   => 'image/gif',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'jpe'   => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'mov'   => 'video/quicktime',
        'mp4'   => 'video/mp4',
        'mpg'   => 'video/mpeg',
        'mpeg'  => 'video/mpeg',
        'otf'   => 'application/x-font-otf',
        'pdf'   => 'application/pdf',
        'pfb'   => 'application/x-font-pfb',
        'pfm'   => 'application/x-font-pfm',
        'png'   => 'image/png',
        'ps'    => 'application/postscript',
        'psb'   => 'image/x-photoshop',
        'psd'   => 'image/x-photoshop',
        'shtml' => 'text/html',
        'shtm'  => 'text/html',
        'svg'   => 'image/svg+xml',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'tsv'   => 'text/tsv',
        'ttf'   => 'application/x-font-ttf',
        'txt'   => 'text/plain',
        'xhtml' => 'application/xhtml+xml',
        'xml'   => 'application/xml'
    );

    /**
     * Image color opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * Image compression
     * @var int|string
     */
    protected $compression = null;

    /**
     * Image filter
     * @var int
     */
    protected $filter = \Imagick::FILTER_LANCZOS;

    /**
     * Image blur
     * @var int
     */
    protected $blur = 1;

    /**
     * Image overlay
     * @var int
     */
    protected $overlay = \Imagick::COMPOSITE_ATOP;

    /**
     * Constructor
     *
     * Instantiate an Imagick file object based on either a pre-existing image
     * file on disk, or a new image file.
     *
     * As of July 28th, 2011, stable testing was successful with the
     * following versions of the required software:
     *
     * ImageMagick 6.5.*
     * Ghostscript 8.70 or 8.71
     * Imagick PHP Extension 3.0.1
     *
     * Any variation in the versions of the required software may contribute to
     * the Pop\Image\Imagick component not functioning properly.
     *
     * @param  string         $img
     * @param  int|string     $w
     * @param  int|string     $h
     * @param  ColorInterface $color
     * @param  array          $types
     * @throws Exception
     * @return void
     */
    public function __construct($img, $w = null, $h = null, ColorInterface $color = null, $types = null)
    {
        $imagickFile = null;
        $imgFile = null;

        // If image passed is a paged images, like a PDF
        if (!file_exists($img) && (strpos($img, '[') !== false)) {
            $imagickFile = $img;
            $imgFile = trim(substr($img, 0, strpos($img, '[')));
            $imgFile .= substr($img, (strpos($img, ']') + 1));
            $img = $imgFile;
        // Else, continue
        } else {
            $imgFile = $img;
            $imagickFile = $img;
        }

        parent::__construct($img, $w, $h, $color, $types);

        // Check to see if Imagick is installed.
        if (!self::isImagickInstalled()) {
            throw new Exception('Error: The Imagick library extension must be installed to use the Imagick adapter.');
        }

        // If image exists, get image info and store in an array.
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $this->resource = new \Imagick($imagickFile);
            $this->setImageInfo();
            $this->setQuality(100);
        // If image does not exists, check to make sure the width and height
        // properties of the new image have been passed.
        } else {
            $this->resource = new \Imagick();

            if ((null === $w) || (null === $h)) {
                throw new Exception('Error: You must define a width and height for a new image object.');
            }

            // Set image object properties.
            $this->width = $w;
            $this->height = $h;
            $this->channels = null;

            $color = (null === $color) ? new Rgb(255, 255, 255) : $color;
            $clr = $this->setColor($color);

            // Create a new image and allocate the background color.
            $this->resource->newImage($w, $h, $clr, $this->ext);

            // Set the quality and create a new, blank image file.
            $this->setQuality(100);
        }

        $this->getImagickInfo();
    }

    /**
     * Check if Imagick is installed.
     *
     * @return boolean
     */
    public static function isImagickInstalled()
    {
        return class_exists('Imagick');
    }

    /**
     * Set the image quality.
     *
     * @param  int $q
     * @return Pop\Image\Imagick
     */
    public function setQuality($q = null)
    {
        $this->quality = (null !== $q) ? (int)$q : null;
        return $this;
    }

    /**
     * Set the opacity.
     *
     * @param  float $opac
     * @return Pop\Image\Imagick
     */
    public function setOpacity($opac)
    {
        $this->opacity = $opac;
        return $this;
    }

    /**
     * Set the image quality.
     *
     * @param  int $comp
     * @return Pop\Image\Imagick
     */
    public function setCompression($comp = null)
    {
        $this->compression = (null !== $comp) ? (int)$comp : null;
        return $this;
    }

    /**
     * Set the image filter.
     *
     * @param  int|string $filter
     * @return Pop\Image\Imagick
     */
    public function setFilter($filter = null)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Set the image blur.
     *
     * @param  int|string $blur
     * @return Pop\Image\Imagick
     */
    public function setBlur($blur = null)
    {
        $this->blur = $blur;
        return $this;
    }

    /**
     * Set the image overlay.
     *
     * @param  int|string $ovr
     * @return Pop\Image\Imagick
     */
    public function setOverlay($ovr = null)
    {
        $this->overlay = $ovr;
        return $this;
    }

    /**
     * Get the Imagick resource to directly interface with the Imagick object.
     *
     * @return Imagick
     */
    public function imagick()
    {
        return $this->resource;
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int|string $wid
     * @return mixed
     */
    public function resizeToWidth($wid)
    {
        $this->setImageInfo();

        $scale = $wid / $this->width;
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int|string $hgt
     * @return mixed
     */
    public function resizeToHeight($hgt)
    {
        $this->setImageInfo();

        $scale = $hgt / $this->height;
        $wid = round($this->width * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument. For example, if the value of $px = 200,
     * and the image is 800px X 600px, then the image will be scaled to
     * 200px X 150px.
     *
     * @param  int|string $px
     * @return Pop\Image\Imagick
     */
    public function resize($px)
    {
        // Determine whether or not the image is landscape or portrait and set
        // the scale, new width and new height accordingly, with the largest
        // dimension being scaled to the value of the $px argument.
        $this->setImageInfo();
        $scale = ($this->width > $this->height) ? ($px / $this->width) : ($px / $this->height);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Scale the image object, allowing for the dimensions to be scaled
     * proportionally to the value of the $scl argument. For example, if the
     * value of $scl = 0.50, and the image is 800px X 600px, then the image
     * will be scaled to 400px X 300px.
     *
     * @param  float|string $scl
     * @return Pop\Image\Imagick
     */
    public function scale($scl)
    {
        // Determine the new width and height of the image based on the
        // value of the $scl argument.
        $this->setImageInfo();
        $wid = round($this->width * $scl);
        $hgt = round($this->height * $scl);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Crop the image object to a image whose dimensions are based on the
     * value of the $wid and $hgt argument. The optional $x and $y arguments
     * allow for the adjustment of the crop to select a certain area of the
     * image to be cropped.
     *
     * @param  int|string $wid
     * @param  int|string $hgt
     * @param  int|string $x
     * @param  int|string $y
     * @return mixed
     */
    public function crop($wid, $hgt, $x = 0, $y = 0)
    {
        // Create a new image output resource.
        $this->resource->cropImage($wid, $hgt, $x, $y);
        $this->setImageInfo();

        return $this;
    }

    /**
     * Crop the image object to a square image whose dimensions are based on the
     * value of the $px argument. The optional $x and $y arguments allow for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped. For example, if the values of $px = 50, $x = 20, $y = 0 are
     * passed, then a 50px X 50px image will be created from the original image,
     * with its origins starting at the (20, 0) x-y coordinates.
     *
     * @param  int|string $px
     * @param  int|string $x
     * @param  int|string $y
     * @return Pop\Image\Imagick
     */
    public function cropThumb($px, $x = 0, $y = 0)
    {
        // Determine whether or not the image is landscape or portrait and set
        // the scale, new width and new height accordingly, with the smallest
        // dimension being scaled to the value of the $px argument to allow
        // for a complete crop.
        $this->setImageInfo();
        $scale = ($this->width > $this->height) ? ($px / $this->height) : ($px / $this->width);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);
        $this->resource->cropImage($px, $px, $x, $y);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Rotate the image object, using simple degrees, i.e. -90,
     * to rotate the image.
     *
     * @param  int|string $deg
     * @return Pop\Image\Imagick
     */
    public function rotate($deg)
    {
        // Create a new image resource and rotate it.
        $color = $this->setColor($this->backgroundColor);
        $this->resource->rotateImage($color, $deg);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Create text within the an image object and output it. A true-type font
     * file is required for the font argument. The size, rotation and position
     * can be set by those respective arguments. This is a useful method for
     * creating CAPTCHA images or rendering sensitive information to the user
     * that cannot or should not be rendered by HTML (i.e. email addresses.)
     *
     * @param  string     $str
     * @param  int|string $size
     * @param  int|string $x
     * @param  int|string $y
     * @param  string     $font
     * @param  int|string $rotate
     * @param  boolean    $stroke
     * @return Pop\Image\Imagick
     */
    public function text($str, $size, $x, $y, $font = 'Arial', $rotate = null, $stroke = false)
    {
        $draw = new \ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($size);
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $rotate) {
            $draw->rotate($rotate);
        }

        if ($stroke) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->annotation($x, $y, $str);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Pop\Image\Imagick
     */
    public function addLine($x1, $y1, $x2, $y2)
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeColor($this->setColor($this->strokeColor));
        $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        $draw->line($x1, $y1, $x2, $y2);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Pop\Image\Imagick
     */
    public function addRectangle($x, $y, $w, $h = null)
    {
        $x2 = $x + $w;
        $y2 = $y + ((null === $h) ? $w : $h);

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $this->strokeWidth) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->rectangle($x, $y, $x2, $y2);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a square to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Pop\Image\Imagick
     */
    public function addSquare($x, $y, $w)
    {
        $this->addRectangle($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add an ellipse to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Pop\Image\Imagick
     */
    public function addEllipse($x, $y, $w, $h = null)
    {
        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $this->strokeWidth) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->ellipse($x, $y, $wid, $hgt, 0, 360);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Pop\Image\Imagick
     */
    public function addCircle($x, $y, $w)
    {
        $this->addEllipse($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add an arc to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Pop\Image\Imagick
     */
    public function addArc($x, $y, $start, $end, $w, $h = null)
    {
        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        $x1 = $w * cos($start / 180 * pi());
        $y1 = $h * sin($start / 180 * pi());
        $x2 = $w * cos($end / 180 * pi());
        $y2 = $h * sin($end / 180 * pi());

        $points = array(
                      array('x' => $x, 'y' => $y),
                      array('x' => $x + $x1, 'y' => $y + $y1),
                      array('x' => $x + $x2, 'y' => $y + $y2)
                  );

        $draw->polygon($points);

        $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
        $this->resource->drawImage($draw);

        if (null !== $this->strokeWidth) {
            $draw = new \ImagickDraw();

            $draw->setFillColor($this->setColor($this->fillColor));
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);

            $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
            $draw->line($x, $y, $x + $x1, $y + $y1);
            $draw->line($x, $y, $x + $x2, $y + $y2);

            $this->resource->drawImage($draw);
        }

        return $this;
    }

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @return Pop\Image\Imagick
     */
    public function addPolygon($points)
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $this->strokeWidth) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->polygon($points);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to adjust the hue of the image.
     *
     * @param  int $h
     * @return Pop\Image\Imagick
     */
    public function hue($h)
    {
        $this->resource->modulateImage(100, 100, $h);
        return $this;
    }

    /**
     * Method to adjust the saturation of the image.
     *
     * @param  int $s
     * @return Pop\Image\Imagick
     */
    public function saturation($s)
    {
        $this->resource->modulateImage(100, $s, 100);
        return $this;
    }

    /**
     * Method to adjust the brightness of the image.
     *
     * @param  int $b
     * @return Pop\Image\Imagick
     */
    public function brightness($b)
    {
        $this->resource->modulateImage($b, 100, 100);
        return $this;
    }

    /**
     * Method to adjust the HSB of the image altogether.
     *
     * @param  int $h
     * @param  int $s
     * @param  int $b
     * @return Pop\Image\Imagick
     */
    public function hsb($h, $s, $b)
    {
        $this->resource->modulateImage($h, $s, $b);
        return $this;
    }

    /**
     * Method to adjust the levels of the image using a 0 - 255 range.
     *
     * @param  int   $black
     * @param  float $gamma
     * @param  int   $white
     * @return Pop\Image\Imagick
     */
    public function level($black, $gamma, $white)
    {
        $quantumRange = $this->resource->getQuantumRange();

        if ($black < 0) {
            $black = 0;
        }
        if ($white > 255) {
            $white = 255;
        }

        $blackPoint = ($black / 255) * $quantumRange['quantumRangeLong'];
        $whitePoint = ($white / 255) * $quantumRange['quantumRangeLong'];

        $this->resource->levelImage($blackPoint, $gamma, $whitePoint);

        return $this;
    }

    /**
     * Method to adjust the contrast of the image.
     *
     * @param  int $amount
     * @return Pop\Image\Imagick
     */
    public function contrast($amount)
    {
        if ($amount > 0) {
            for ($i = 1; $i <= $amount; $i++) {
                $this->resource->contrastImage(1);
            }
        } else if ($amount < 0) {
            for ($i = -1; $i >= $amount; $i--) {
                $this->resource->contrastImage(0);
            }
        }

        return $this;
    }

    /**
     * Method to sharpen the image.
     *
     * @param  int $radius
     * @param  int $sigma
     * @return Pop\Image\Imagick
     */
    public function sharpen($radius = 0, $sigma = 0)
    {
        $this->resource->sharpenImage($radius, $sigma);
        return $this;
    }

    /**
     * Method to blur the image.
     *
     * @param  int $radius
     * @param  int $sigma
     * @param  int $angle
     * @param  int $type
     * @return Pop\Image\Imagick
     */
    public function blur($radius = 0, $sigma = 0, $angle = 0, $type = Imagick::BLUR)
    {
        switch ($type) {
            case self::BLUR:
                $this->resource->blurImage($radius, $sigma);
                break;
            case self::GAUSSIAN_BLUR:
                $this->resource->gaussianBlurImage($radius, $sigma);
                break;
            case self::MOTION_BLUR:
                $this->resource->motionBlurImage($radius, $sigma, $angle);
                break;
            case self::RADIAL_BLUR:
                $this->resource->radialBlurImage($angle);
                break;
        }

        return $this;
    }

    /**
     * Method to add a border to the image.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $type
     * @return Pop\Image\Imagick
     */
    public function border($w, $h = null, $type = Imagick::INNER_BORDER)
    {
        $h = (null === $h) ? $w : $h;

        if ($type == self::INNER_BORDER) {
            $this->setStrokeWidth(($h * 2));
            $this->addLine(0, 0, $this->width, 0);
            $this->addLine(0, $this->height, $this->width, $this->height);
            $this->setStrokeWidth(($w * 2));
            $this->addLine(0, 0, 0, $this->height);
            $this->addLine($this->width, 0, $this->width, $this->height);
        } else {
            $this->resource->borderImage($this->setColor($this->strokeColor), $w, $h);
        }

        return $this;
    }

    /**
     * Overlay an image onto the current image.
     *
     * @param  string     $ovr
     * @param  int|string $x
     * @param  int|string $y
     * @return Pop\Image\Imagick
     */
    public function overlay($ovr, $x = 0, $y = 0)
    {
        $overlayImage = new \Imagick($ovr);
        if ($this->opacity < 1) {
            $overlayImage->setImageOpacity($this->opacity);
        }

        $this->resource->compositeImage($overlayImage, $this->overlay, $x, $y);
        return $this;
    }

    /**
     * Method to colorize the image with the color passed.
     *
     * @param  ColorInterface $color
     * @return Pop\Image\Imagick
     */
    public function colorize(ColorInterface $color)
    {
        $this->resource->colorizeImage($color->getRgb(Color::STRING, true), $this->opacity);
        return $this;
    }

    /**
     * Method to invert the image (create a negative.)
     *
     * @return Pop\Image\Imagick
     */
    public function invert()
    {
        $this->resource->negateImage(false);
        return $this;
    }

    /**
     * Method to flip the image over the x-axis.
     *
     * @return Pop\Image\Imagick
     */
    public function flip()
    {
        $this->resource->flipImage();
        return $this;
    }

    /**
     * Method to flip the image over the x-axis.
     *
     * @return Pop\Image\Imagick
     */
    public function flop()
    {
        $this->resource->flopImage();
        return $this;
    }

    /**
     * Flatten the image layers
     *
     * @return Pop\Image\Imagick
     */
    public function flatten()
    {
        $this->resource->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        return $this;
    }

    /**
     * Apply an oil paint effect to the image using the pixel radius threshold
     *
     * @param  int $radius
     * @return Pop\Image\Imagick
     */
    public function paint($radius)
    {
        $this->resource->oilPaintImage($radius);
        return $this;
    }

    /**
     * Apply a posterize effect to the image
     *
     * @param  int     $levels
     * @param  boolean $dither
     * @return Pop\Image\Imagick
     */
    public function posterize($levels, $dither = false)
    {
        $this->resource->posterizeImage($levels, $dither);
        return $this;
    }

    /**
     * Apply a noise effect to the image
     *
     * @param  int $type
     * @return Pop\Image\Imagick
     */
    public function noise($type = \Imagick::NOISE_MULTIPLICATIVEGAUSSIAN)
    {
        $this->resource->addNoiseImage($type);
        return $this;
    }

    /**
     * Apply a diffusion effect to the image
     *
     * @param  int $radius
     * @return Pop\Image\Imagick
     */
    public function diffuse($radius)
    {
        $this->resource->spreadImage($radius);
        return $this;
    }

    /**
     * Apply a skew effect to the image
     *
     * @param  ColorInterface $color
     * @param  int            $x
     * @param  int            $y
     * @return Pop\Image\Imagick
     */
    public function skew(ColorInterface $color, $x, $y)
    {
        $this->resource->shearImage($color->getRgb(Color::STRING, true), $x, $y);
        return $this;
    }

    /**
     * Apply a mosiac pixelate effect to the image
     *
     * @param  int $w
     * @param  int $h
     * @return Pop\Image\Imagick
     */
    public function pixelate($w, $h = null)
    {
        $x = $this->width / $w;
        $y = $this->height / ((null === $h) ? $w : $h);

        $this->resource->scaleImage($x, $y);
        $this->resource->scaleImage($this->width, $this->height);

        return $this;
    }

    /**
     * Apply a pencil/sketch effect to the image
     *
     * @param  int $radius
     * @param  int $sigma
     * @param  int $angle
     * @return Pop\Image\Imagick
     */
    public function pencil($radius, $sigma, $angle)
    {
        $this->resource->sketchImage($radius, $sigma, $angle);
        return $this;
    }

    /**
     * Apply a swirl effect to the image
     *
     * @param  int $degrees
     * @return Pop\Image\Imagick
     */
    public function swirl($degrees)
    {
        $this->resource->swirlImage($degrees);
        return $this;
    }

    /**
     * Apply a wave effect to the image
     *
     * @param  int $amp
     * @param  int $length
     * @return Pop\Image\Imagick
     */
    public function wave($amp, $length)
    {
        $this->resource->waveImage($amp, $length);
        return $this;
    }

    /**
     * Return the number of colors in the palette of indexed images.
     *
     * @return int
     */
    public function colorTotal()
    {
        return $this->resource->getImageColors();
    }

    /**
     * Return all of the colors in the palette in an array format, omitting any
     * repeats. It is strongly advised that this method only be used for smaller
     * image files, preferably with small palettes, as any large images with
     * many colors will cause this method to run slowly. Default format of the
     * values in the returned array is the 6-digit HEX value, but if 'RGB' is
     * passed, then the format of the values in the returned array will be
     * 'R,G,B', i.e. '235,123,12'.
     *
     * @param  string $format
     * @return array
     */
    public function getColors($format = 'HEX')
    {
        // Initialize the colors array and the image resource.
        $colors = array();

        // Loop through each pixel of the image, recording the color result
        // in the color array.
        for ($h = 0; $h < $this->height; $h++) {
            for ($w = 0; $w < $this->width; $w++) {
                $point = $this->resource->getImagePixelColor($w, $h);
                $color = $point->getColor();

                // Convert to the proper HEX or RGB format.
                if ($format == 'HEX') {
                    $rgb = sprintf('%02s', dechex($color['r'])) . sprintf('%02s', dechex($color['g'])) . sprintf('%02s', dechex($color['b']));
                } else {
                    $rgb = $color['r'] . "," . $color['g'] . "," . $color['b'];
                }
                // If the color is not already in the array, add to it.
                if (!in_array($rgb, $colors)) {
                    $colors[] = $rgb;
                }
            }
        }

        // Return the colors array.
        return $colors;
    }

    /**
     * Convert the image object to the new specified image type.
     *
     * @param  string     $type
     * @throws Exception
     * @return Pop\Image\Imagick
     */
    public function convert($type)
    {
        $type = strtolower($type);

        // Check if the requested image type is supported.
        if (!array_key_exists($type, $this->allowed)) {
            throw new Exception('Error: That image type is not supported.');
        // Check if the image is already the requested image type.
        } else if (strtolower($this->ext) == $type) {
            throw new Exception('Error: This image file is already a ' . strtoupper($type) . ' image file.');
        }

        // Else, save the image as the new type.
        $old = $this->ext;
        $this->ext = $type;
        $this->mime = $this->allowed[$this->ext];
        $this->fullpath = $this->dir . $this->filename . '.' . $this->ext;
        $this->basename = basename($this->fullpath);

        if (($old == 'psd') || ($old == 'tif') || ($old == 'tiff')) {
            $this->flatten();
        }
        $this->resource->setImageFormat($type);

        return $this;
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return Pop\Image\Imagick
     */
    public function output($download = false)
    {
        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = array(
            'Content-type' => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        );

        $response = new Response(200, $headers);

        if ($_SERVER['SERVER_PORT'] == 443) {
            $response->setSslHeaders();
        }

        if (null !== $this->compression) {
            $this->resource->setImageCompression($this->compression);
        }
        if (null !== $this->quality) {
            $this->resource->setImageCompressionQuality($this->quality);
        }

        $response->sendHeaders();
        echo $this->resource;

        return $this;
    }

    /**
     * Save the image object to disk.
     *
     * @param  string  $to
     * @param  boolean $append
     * @return void
     */
    public function save($to = null, $append = false)
    {
        if (null !== $this->compression) {
            $this->resource->setImageCompression($this->compression);
        }
        if (null !== $this->quality) {
            $this->resource->setImageCompressionQuality($this->quality);
        }
        $img = (null !== $to) ? $to : $this->fullpath;
        $this->resource->writeImage($img);

        clearstatcache();

        $this->setFile($img);
        $this->setImageInfo();

        return $this;
    }

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $file
     * @return void
     */
    public function destroy($file = false)
    {
        $this->resource->clear();
        $this->resource->destroy();

        // Clear PHP's file status cache.
        clearstatcache();

        // If the $file flag is passed, delete the image file.
        if ($file) {
            $this->delete();
        }
    }

    /**
     * Set the current object formats against the supported formats of Imagick.
     *
     * @return void
     */
    public function setFormats()
    {
        $formats = $this->getFormats();

        foreach ($formats as $format) {
            $frmt = strtolower($format);
            if (!array_key_exists($frmt, $this->allowed)) {
                $this->allowed[$frmt] = 'image/' . $frmt;
            }
        }

        ksort($this->allowed);
    }

    /**
     * Get the array of supported formats of Imagick.
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->resource->queryFormats();
    }

    /**
     * Get the number of supported formats of Imagick.
     *
     * @return int
     */
    public function getNumberOfFormats()
    {
        return count($this->resource->queryFormats());
    }

    /**
     * Get Imagick Info.
     *
     * @return void
     */
    protected function getImagickInfo()
    {
        $imagickVersion = $this->resource->getVersion();
        $this->versionString = trim(substr($imagickVersion['versionString'], 0, stripos($imagickVersion['versionString'], 'http://')));
        $this->version = substr($this->versionString, (strpos($this->versionString, ' ') + 1));
        $this->version = substr($this->version, 0, strpos($this->version, '-'));
    }

    /**
     * Set the image info
     *
     * @return void
     */
    protected function setImageInfo()
    {
        // Set image object properties.
        $this->width = $this->resource->getImageWidth();
        $this->height = $this->resource->getImageHeight();
        $this->depth = $this->resource->getImageDepth();
        $this->quality = null;

        $this->alpha = ($this->resource->getImageAlphaChannel() == 1) ? true : false;
        $colorSpace = $this->resource->getImageColorspace();
        $type = $this->resource->getImageType();

        switch ($colorSpace) {
            case \Imagick::COLORSPACE_UNDEFINED:
                $this->channels = 0;
                $this->mode = '';
                break;
            case \Imagick::COLORSPACE_RGB:
                if ($type == \Imagick::IMGTYPE_PALETTE) {
                    $this->channels = 3;
                    $this->mode = 'Indexed';
                } else if ($type == \Imagick::IMGTYPE_PALETTEMATTE) {
                    $this->channels = 3;
                    $this->mode = 'Indexed';
                } else if ($type == \Imagick::IMGTYPE_GRAYSCALE) {
                    $this->channels = 1;
                    $this->mode = 'Gray';
                } else if ($type == \Imagick::IMGTYPE_GRAYSCALEMATTE) {
                    $this->channels = 1;
                    $this->mode = 'Gray';
                } else {
                    $this->channels = 3;
                    $this->mode = 'RGB';
                }
                break;
            case \Imagick::COLORSPACE_GRAY:
                $this->channels = 1;
                $this->mode = (($type == \Imagick::IMGTYPE_PALETTE) || ($type == \Imagick::IMGTYPE_PALETTEMATTE)) ? 'Indexed' : 'Gray';
                break;
            case \Imagick::COLORSPACE_TRANSPARENT:
                $this->channels = 1;
                $this->mode = 'Transparent';
                break;
            case \Imagick::COLORSPACE_OHTA:
                $this->channels = 3;
                $this->mode = 'OHTA';
                break;
            case \Imagick::COLORSPACE_LAB:
                $this->channels = 3;
                $this->mode = 'LAB';
                break;
            case \Imagick::COLORSPACE_XYZ:
                $this->channels = 3;
                $this->mode = 'XYZ';
                break;
            case \Imagick::COLORSPACE_YCBCR:
                $this->channels = 3;
                $this->mode = 'YCbCr';
                break;
            case \Imagick::COLORSPACE_YCC:
                $this->channels = 3;
                $this->mode = 'YCC';
                break;
            case \Imagick::COLORSPACE_YIQ:
                $this->channels = 3;
                $this->mode = 'YIQ';
                break;
            case \Imagick::COLORSPACE_YPBPR:
                $this->channels = 3;
                $this->mode = 'YPbPr';
                break;
            case \Imagick::COLORSPACE_YUV:
                $this->channels = 3;
                $this->mode = 'YUV';
                break;
            case \Imagick::COLORSPACE_CMYK:
                $this->channels = 4;
                $this->mode = 'CMYK';
                break;
            case \Imagick::COLORSPACE_SRGB:
                $this->channels = 3;
                $this->mode = 'sRGB';
                break;
            case \Imagick::COLORSPACE_HSB:
                $this->channels = 3;
                $this->mode = 'HSB';
                break;
            case \Imagick::COLORSPACE_HSL:
                $this->channels = 3;
                $this->mode = 'HSL';
                break;
            case \Imagick::COLORSPACE_HWB:
                $this->channels = 3;
                $this->mode = 'HWB';
                break;
            case \Imagick::COLORSPACE_REC601LUMA:
                $this->channels = 3;
                $this->mode = 'Rec601';
                break;
            case \Imagick::COLORSPACE_REC709LUMA:
                $this->channels = 3;
                $this->mode = 'Rec709';
                break;
            case \Imagick::COLORSPACE_LOG:
                $this->channels = 3;
                $this->mode = 'LOG';
                break;
            case \Imagick::COLORSPACE_CMY:
                $this->channels = 3;
                $this->mode = 'CMY';
                break;
        }
    }

    /**
     * Set and return a color identifier.
     *
     * @param  ColorInterface $color
     * @throws Exception
     * @return mixed
     */
    protected function setColor(ColorInterface $color = null)
    {
        $clr = (null !== $color) ? $color->getRgb(Color::STRING, true) : 'rgb(0,0,0)';
        return new \ImagickPixel($clr);
    }

}
