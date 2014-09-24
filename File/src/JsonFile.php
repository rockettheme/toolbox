<?php
namespace RocketTheme\Toolbox\File;

/**
 * Implements Json File reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class JsonFile extends File
{
    /**
     * @var string
     */
    protected $extension = '.json';

    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Check contents and make sure it is in correct format.
     *
     * @param array $var
     * @return array
     */
    protected function check($var)
    {
        return (array) $var;
    }

    /**
     * Encode contents into RAW string.
     *
     * @param string $var
     * @return string
     */
    protected function encode($var)
    {
        return (string) json_encode($var);
    }

    /**
     * Decode RAW string into contents.
     *
     * @param string $var
     * @return array mixed
     */
    protected function decode($var)
    {
        return (array) json_decode($var, true);
    }
}
