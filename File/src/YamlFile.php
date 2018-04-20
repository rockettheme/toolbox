<?php
namespace RocketTheme\Toolbox\File;

use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Exception\ParseException;
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
    static protected $instances = [];

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
     * @param array $var
     * @return string
     * @throws DumpException
     */
    protected function encode($var)
    {
        return (string) YamlParser::dump($var, $this->setting('inline', 5), $this->setting('indent', 2), true, false);
    }

    /**
     * Decode RAW string into contents.
     *
     * @param string $var
     * @return array mixed
     * @throws ParseException
     */
    protected function decode($var)
    {
        if ($this->setting('compat', true)) {
            // Fix illegal @ start character.
            $var = preg_replace('/ (@[\w\.\-]*)/', " '\${1}'", $var);
        }

        // Try native PECL YAML PHP extension first if available.
        if ($this->setting('native') && function_exists('yaml_parse')) {
            // Safely decode YAML.
            $saved = @ini_get('yaml.decode_php');
            @ini_set('yaml.decode_php', 0);
            $data = @yaml_parse($var);
            @ini_set('yaml.decode_php', $saved);

            return (array) $data;
        }

        return (array) YamlParser::parse($var);
    }
}
