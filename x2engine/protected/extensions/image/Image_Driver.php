<?php
/**
 * Image API driver.
 *
 * $Id: Image.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Image
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Image_Driver
{

    // Reference to the current image
    protected $image;

    // Reference to the temporary processing image
    protected $tmp_image;

    // Processing errors
    protected $errors = array();

    /**
     * Executes a set of actions, defined in pairs.
     *
     * @param   $actions array actions
     * @return  boolean
     */
    public function execute($actions)
    {
        foreach ($actions as $func => $args) {
            if (!$this->$func($args))
                return FALSE;
        }

        return TRUE;
    }

    /**
     * Sanitize and normalize a geometry array based on the temporary image
     * width and height. Valid properties are: width, height, top, left.
     *
     * @param   $geometry array  geometry properties
     * @return  void
     */
    protected function sanitize_geometry(& $geometry)
    {
        list($width, $height) = $this->properties();

        // Turn off error reporting
        $reporting = error_reporting(0);

        // Width and height cannot exceed current image size
        $geometry['width'] = min($geometry['width'], $width);
        $geometry['height'] = min($geometry['height'], $height);

        // Set standard coordinates if given, otherwise use pixel values
        if ($geometry['top'] === 'center') {
            $geometry['top'] = floor(($height / 2) - ($geometry['height'] / 2));
        } elseif ($geometry['top'] === 'top') {
            $geometry['top'] = 0;
        }
        elseif ($geometry['top'] === 'bottom') {
            $geometry['top'] = $height - $geometry['height'];
        }

        // Set standard coordinates if given, otherwise use pixel values
        if ($geometry['left'] === 'center') {
            $geometry['left'] = floor(($width / 2) - ($geometry['width'] / 2));
        } elseif ($geometry['left'] === 'left') {
            $geometry['left'] = 0;
        }
        elseif ($geometry['left'] === 'right') {
            $geometry['left'] = $width - $geometry['height'];
        }

        // Restore error reporting
        error_reporting($reporting);
    }

    /**
     * Return the current width and height of the temporary image. This is mainly
     * needed for sanitizing the geometry.
     *
     * @return  array  width, height
     */
    abstract protected function properties();

    /**
     * Process an image with a set of actions.
     *
     * @param   $image string   image filename
     * @param   $actions array    actions to execute
     * @param   $dir string   destination directory path
     * @param   $file string   destination filename
     * @return  boolean
     */
    abstract public function process($image, $actions, $dir, $file);

    /**
     * Flip an image. Valid directions are horizontal and vertical.
     *
     * @param   $direction int direction to flip
     * @return  boolean
     */
    abstract function flip($direction);

    /**
     * Crop an image. Valid properties are: width, height, top, left.
     *
     * @param   $properties array     new properties
     * @return  boolean
     */
    abstract function crop($properties);

    /**
     * Resize an image. Valid properties are: width, height, and master.
     *
     * @param   $properties array     new properties
     * @return  boolean
     */
    abstract public function resize($properties);

    /**
     * Rotate an image. Valid amounts are -180 to 180.
     *
     * @param   $amount integer   amount to rotate
     * @return  boolean
     */
    abstract public function rotate($amount);

    /**
     * Sharpen and image. Valid amounts are 1 to 100.
     *
     * @param   $amount integer  amount to sharpen
     * @return  boolean
     */
    abstract public function sharpen($amount);

    /**
     * Grayscale an image.
     *
     * @param $unused
     * @return boolean
     */
    abstract public function grayscale($unused);

    /**
     * Emboss an image.
     *
     * @author parcouss
     * @param $radius int only used with imagemagick
     * @return boolean
     */
    abstract public function emboss($radius);

    /**
     * Negate an image.
     *
     * @author parcouss
     * @param $unused
     * @return boolean
     */
    abstract public function negate($unused);

    abstract public function watermark($params);
} // End Image Driver