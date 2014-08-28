<?php

namespace RocketTheme\Toolbox\ResourceLocator;

/**
 * Defines ResourceLocatorInterface.
 *
 * @package RocketTheme\Toolbox\ResourceLocator
 * @author RocketTheme
 * @license MIT
 */
interface ResourceLocatorInterface
{
    /**
     * Alias for findResource()
     *
     * @param $uri
     * @return string|bool
     */
    public function __invoke($uri);

    /**
     * @param  string $uri
     * @param  bool   $absolute
     * @return string|bool
     */
    public function findResource($uri, $absolute = true);

    /**
     * @param  string $uri
     * @param  bool   $absolute
     * @return array
     */
    public function findResources($uri, $absolute = true);
}
