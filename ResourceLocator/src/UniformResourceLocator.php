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
     * Reset locator by removing all the schemes.
     *
     * @return $this
     */
    public function reset()
    {
        $this->schemes = [];
        $this->cache = [];

        return $this;
    }

    /**
     * Add new paths to the scheme.
     *
     * @param string $scheme
     * @param string $prefix
     * @param string|array $paths
     * @param bool  $override  Add path as override.
     * @throws \BadMethodCallException
     */
    public function addPath($scheme, $prefix, $paths, $override = false)
    {
        $list = [];
        foreach((array) $paths as $path) {
            if (is_array($path)) {
                // Support stream lookup in ['theme', 'path/to'] format.
                if (count($path) != 2) {
                    throw new \BadMethodCallException('Invalid stream path given.');
                }
                $list[] = $path;
            } elseif (strstr($path, '://')) {
                // Support stream lookup in 'theme://path/to' format.
                $list[] = explode('://', $path, 2);
            } else {
                // Support relative paths.
                $path = trim($path, '/');
                if (file_exists("{$this->base}/{$path}")) {
                    $list[] = $path;
                }
            }
        }

        if (isset($this->schemes[$scheme][$prefix])) {
            $list = $override
                ? array_merge($this->schemes[$scheme][$prefix], $list)
                : array_merge($list, $this->schemes[$scheme][$prefix]);
        }

        $this->schemes[$scheme][$prefix] = $list;

        // Sort in reverse order to get longer prefixes to be matched first.
        krsort($this->schemes[$scheme]);

        $this->cache = [];
    }

    /**
     * Return base directory.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }


    /**
     * Return true if scheme has been defined.
     *
     * @param string $name
     * @return bool
     */
    public function schemeExists($name)
    {
        return isset($this->schemes[$name]);
    }

    /**
     * Return defined schemes.
     *
     * @return array
     */
    public function getSchemes()
    {
        return array_keys($this->schemes);
    }

    /**
     * Return all scheme lookup paths.
     *
     * @param $scheme
     * @return array
     */
    public function getPaths($scheme)
    {
        return isset($this->schemes[$scheme]) ? $this->schemes[$scheme] : [];
    }

    /**
     * @param $uri
     * @return string|bool
     * @throws \BadMethodCallException
     */
    public function __invoke($uri)
    {
        if (!is_string($uri)) {
            throw new \BadMethodCallException('Invalid parameter $uri.');
        }
        return $this->find($uri, false, true, false);
    }

    /**
     * Find highest priority instance from a resource.
     *
     * @param  string $uri      Input URI to be searched.
     * @param  bool   $absolute Whether to return absolute path.
     * @param  bool   $first    Whether to return first path even if it doesn't exist.
     * @throws \BadMethodCallException
     * @return string|bool
     */
    public function findResource($uri, $absolute = true, $first = false)
    {
        if (!is_string($uri)) {
            throw new \BadMethodCallException('Invalid parameter $uri.');
        }
        return $this->find($uri, false, $absolute, $first);
    }

    /**
     * Find all instances from a resource.
     *
     * @param  string $uri      Input URI to be searched.
     * @param  bool   $absolute Whether to return absolute path.
     * @param  bool   $all      Whether to return all paths even if they don't exist.
     * @throws \BadMethodCallException
     * @return array
     */
    public function findResources($uri, $absolute = true, $all = false)
    {
        if (!is_string($uri)) {
            throw new \BadMethodCallException('Invalid parameter $uri.');
        }
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

        if (!$file && $scheme == 'file') {
            $file = $this->base;
        }

        return [$scheme, $file];
    }

    /**
     * @param  string|array $uri
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
        if (is_string($uri)) {
            // Local caching: make sure that the function gets only called at once for each file.
            $key = $uri .'@'. (int) $array . (int) $absolute . (int) $all;

            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }

            list ($scheme, $file) = $this->parseResource($uri);

        } else {
            // Accept also internal $uri format: [scheme, file].
            list ($scheme, $file) = $uri;
        }

        if (!isset($this->schemes[$scheme])) {
            throw new \InvalidArgumentException("Invalid resource {$scheme}://");
        }

        $results = $array ? [] : false;
        foreach ($this->schemes[$scheme] as $prefix => $paths) {
            if ($prefix && strpos($file, $prefix) !== 0) {
                continue;
            }

            foreach ($paths as $path) {
                $filePath = '/' . ltrim(substr($file, strlen($prefix)), '\/');
                if (is_array($path)) {
                    // Handle scheme lookup.
                    $path[1] = trim($path[1] . $filePath, '/');
                    $found = $this->find($path, $array, $absolute, $all);
                    if ($found) {
                        if (!$array) {
                            $results = $found;
                            break 2;
                        }
                        $results = array_merge($results, $found);
                    }
                } else {
                    // Handle relative path lookup.
                    $path = trim($path . $filePath, '/');
                    $lookup = $this->base . '/' . $path;

                    if ($all || file_exists($lookup)) {
                        $current = $absolute ? $lookup : $path;
                        if (!$array) {
                            $results = $current;
                            break 2;
                        }
                        $results[] = $current;
                    }
                }
            }
        }

        if (isset($key)) {
            $this->cache[$key] = $results;
        }

        return $results;
    }
}
