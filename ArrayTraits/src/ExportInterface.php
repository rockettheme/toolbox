<?php
namespace RocketTheme\Toolbox\ArrayTraits;

/**
 * Defines Export interface.
 *
 * @package RocketTheme\Toolbox\ArrayTraits
 * @author RocketTheme
 * @license MIT
 */
interface ExportInterface
{
    /**
     * Convert object into an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Convert object into YAML string.
     *
     * @return string
     */
    public function toYaml();

    /**
     * Convert object into JSON string.
     *
     * @return string
     */
    public function toJson();
}
