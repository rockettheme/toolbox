<?php

namespace RocketTheme\Toolbox\ResourceLocator;

/**
 * Implements Uniform Resource Location.
 *
 * @package RocketTheme\Toolbox\ResourceLocator
 * @author RocketTheme
 * @license MIT
 *
 * @link http://webmozarts.com/2013/06/19/the-power-of-uniform-resource-location-in-php/
 */
class UniformResourceLocator implements ResourceLocatorInterface
{
    /**
     * @var string  Base URL for all the streams.
     */
    public $base;

    /**
     * @var array
     */
    protected $schemes = [];

    /**
     * @var array
     */
    protected $cache = [];

    public function __construct($base = null)
    {
        $this->base = rtrim($base ?: getcwd(), '/');
    }

    /**
     * Add new paths to the scheme.
     *
     * @param string $scheme
     * @param string $prefix
     * @param string|array $paths
     */
    public function addPath($scheme, $prefix, $paths)
    {
        $list = [];
        foreach((array) $paths as $path) {
            $path = trim($path, '/');
            if (strstr($path, '://')) {
                $items = $this->find($path, true, false, false);
                if ($items) {
                    $list = array_merge($list, $items);
                }
            } elseif (is_dir("{$this->base}/{$path}")) {
                $list[] = $path;
            }
        }

        if (isset($this->schemes[$scheme][$prefix])) {
            $list = array_merge($list, $this->schemes[$scheme][$prefix]);
        }

        if ($list) {
            $this->schemes[$scheme][$prefix] = $list;
        }

        // Sort in reverse order to get longer prefixes to be matched first.
        if (isset($this->schemes[$scheme])) {
            krsort($this->schemes[$scheme]);
        }
        $this->cache = [];
    }

    public function getSchemes()
    {
        return array_keys($this->schemes);
    }

    /**
     * @param $uri
     * @return string|bool
     */
    public function __invoke($uri)
    {
        return $this->find($uri, false, true, false);
    }

    /**
     * Find highest priority instance from a resource.
     *
     * @param  string $uri      Input URI to be searched.
     * @param  bool   $absolute Whether to return absolute path.
     * @param  bool   $first    Whether to return first path even if it doesn't exist.
     * @return string|bool
     */
    public function findResource($uri, $absolute = true, $first = false)
    {
        return $this->find($uri, false, $absolute, $first);
    }

    /**
     * Find all instances from a resource.
     *
     * @param  string $uri      Input URI to be searched.
     * @param  bool   $absolute Whether to return absolute path.
     * @param  bool   $all      Whether to return all paths even if they don't exist.
     * @return array
     */
    public function findResources($uri, $absolute = true, $all = false)
    {
        return $this->find($uri, true, $absolute, $all);
    }

    /**
     * Parse resource.
     *
     * @param $uri
     * @return array
     * @throws \InvalidArgumentException
     * @internal
     */
    protected function parseResource($uri)
    {
        $segments = explode('://', $uri, 2);
        $file = array_pop($segments);
        $scheme = array_pop($segments);

        if (!$scheme) {
            $scheme = 'file';
        }

        if (!isset($this->schemes[$scheme])) {
            throw new \InvalidArgumentException("Invalid resource {$scheme}://");
        }
        if (!$file && $scheme == 'file') {
            $file = $this->base;
        }

        return [$file, $scheme];
    }

    /**
     * @param  string $uri
     * @param  bool $array
     * @param  bool $absolute
     * @param  bool $all
     *
     * @throws \InvalidArgumentException
     * @return array|string|bool
     * @internal
     */
    protected function find($uri, $array, $absolute, $all)
    {
        // Local caching: make sure that the function gets only called at once for each file.
        $key = $uri .'@'. (int) $array . (int) $absolute;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        list ($file, $scheme) = $this->parseResource($uri);

        $results = $array ? [] : false;
        foreach ($this->schemes[$scheme] as $prefix => $paths) {
            if ($prefix && strpos($file, $prefix) !== 0) {
                continue;
            }

            foreach ($paths as $path) {
                $filename = $path . '/' . ltrim(substr($file, strlen($prefix)), '\/');
                $lookup = $this->base . '/' . trim($filename, '/');

                if ($all || file_exists($lookup)) {
                    $current = $absolute ? $lookup : $filename;
                    if (!$array) {
                        $results = $current;
                        break 2;
                    }
                    $results[] = $current;
                }
            }
        }

        $this->cache[$key] = $results;
        return $results;
    }
}
