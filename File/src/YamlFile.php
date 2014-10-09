<?php
namespace RocketTheme\Toolbox\File;

use \Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Implements YAML File reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class YamlFile extends File
{
    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->extension = '.yaml';
    }

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
        return (string) YamlParser::dump($var, 3, 2);
    }

    /**
     * Decode RAW string into contents.
     *
     * @param string $var
     * @return array mixed
     */
    protected function decode($var)
    {
        return (array) YamlParser::parse($var);
    }
}
