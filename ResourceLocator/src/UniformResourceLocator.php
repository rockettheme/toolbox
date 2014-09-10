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
    public $base;

    /**
     * @var array
     */
    protected $schemes = [];

    protected $cache = [];

    public function __construct($base = null)
    {
        $this->base = $base ?: getcwd();
    }

    /**
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
                $list = array_merge($list, $this->find($path, true, false));
            } else {
                $list[] = $path;
            }
        }

        if (isset($this->schemes[$scheme][$prefix])) {
            $list = array_merge($list, $this->schemes[$scheme][$prefix]);
        }

        $this->schemes[$scheme][$prefix] = $list;

        // Sort in reverse order to get longer prefixes to be matched first.
        krsort($this->schemes[$scheme]);

        $this->cache = [];
    }

    /**
     * @param $uri
     * @return string|bool
     */
    public function __invoke($uri)
    {
        return $this->find($uri, false, true);
    }

    /**
     * @param  string $uri
     * @param  bool   $absolute
     * @return string|bool
     */
    public function findResource($uri, $absolute = true)
    {
        return $this->find($uri, false, $absolute);
    }

    /**
     * @param  string $uri
     * @param  bool   $absolute
     * @return array
     */
    public function findResources($uri, $absolute = true)
    {
        return $this->find($uri, true, $absolute);
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
     * @param  bool   $absolute
     * @param  bool $array
     *
     * @throws \InvalidArgumentException
     * @return array|string|bool
     * @internal
     */
    protected function find($uri, $array, $absolute)
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
                $lookup = $this->base . '/' . $filename;

                if (file_exists($lookup)) {
                    if (!$array) {
                        $results = $absolute ? $lookup : $filename;
                        break;
                    }
                    $results[] = $absolute ? $lookup : $filename;
                }
            }
        }

        $this->cache[$key] = $results;
        return $results;
    }
}
