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
        // Normalize base path.
        $this->base = rtrim(str_replace('\\', '/', $base ?: getcwd()), '/');
    }

    /**
     * Return iterator for the resource URI.
     *
     * @param  string $uri
     * @param  int    $flags    See constants from FilesystemIterator class.
     * @return UniformResourceIterator
     */
    public function getIterator($uri, $flags = null)
    {
        return new UniformResourceIterator($uri, $flags, $this);
    }

    /**
     * Return recursive iterator for the resource URI.
     *
     * @param  string $uri
     * @param  int    $flags    See constants from FilesystemIterator class.
     * @return RecursiveUniformResourceIterator
     */
    public function getRecursiveIterator($uri, $flags = null)
    {
        return new RecursiveUniformResourceIterator($uri, $flags, $this);
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
     * Reset a locator scheme
     *
     * @param string $scheme The scheme to reset
     *
     * @return $this
     */
    public function resetScheme($scheme)
    {
        $this->schemes[$scheme] = [];
        $this->cache = [];

        return $this;
    }

    /**
     * Add new paths to the scheme.
     *
     * @param string $scheme
     * @param string $prefix
     * @param string|array $paths
     * @param bool|string  $override  True to add path as override, string
     * @param bool  $force     True to add paths even if them do not exist.
     * @throws \BadMethodCallException
     */
    public function addPath($scheme, $prefix, $paths, $override = false, $force = false)
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
                $stream = explode('://', $path, 2);
                $stream[1] = trim($stream[1], '/');

                $list[] = $stream;
            } else {
                // Normalize path.
                $path = rtrim(str_replace('\\', '/', $path), '/');
                if ($force || @file_exists("{$this->base}/{$path}") || @file_exists($path)) {
                    // Support for absolute and relative paths.
                    $list[] = $path;
                }
            }
        }

        if (isset($this->schemes[$scheme][$prefix])) {
            $paths = $this->schemes[$scheme][$prefix];
            if (!$override || $override == 1) {
                $list = $override ? array_merge($paths, $list) : array_merge($list, $paths);
            } else {
                $location = array_search($override, $paths) ?: count($paths);
                array_splice($paths, $location, 0, $list);
                $list = $paths;
            }
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
     * @param  string $uri
     * @return string|bool
     * @throws \BadMethodCallException
     */
    public function __invoke($uri)
    {
        if (!is_string($uri)) {
            throw new \BadMethodCallException('Invalid parameter $uri.');
        }
        return $this->findCached($uri, false, true, false);
    }

    /**
     * Returns true if uri is resolvable by using locator.
     *
     * @param  string $uri
     * @return bool
     */
    public function isStream($uri)
    {
        list ($scheme,) = $this->parseResource($uri);

        return $this->schemeExists($scheme);
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
        return $this->findCached($uri, false, $absolute, $first);
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

        return $this->findCached($uri, true, $absolute, $all);
    }

    /**
     * Find all instances from a list of resources.
     *
     * @param  array  $uris     Input URIs to be searched.
     * @param  bool   $absolute Whether to return absolute path.
     * @param  bool   $all      Whether to return all paths even if they don't exist.
     * @throws \BadMethodCallException
     * @return array
     */
    public function mergeResources(array $uris, $absolute = true, $all = false)
    {
        $uris = array_unique($uris);

        $list = [];
        foreach ($uris as $uri) {
            $list = array_merge($list, $this->findResources($uri, $absolute, $all));
        }

        return $list;
    }

    /**
     * Pre-fill cache by a stream.
     *
     * @param string $uri
     * @return $this
     */
    public function fillCache($uri)
    {
        $cacheKey = $uri . '@cache';

        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = true;

            $iterator = new \RecursiveIteratorIterator($this->getRecursiveIterator($uri), \RecursiveIteratorIterator::SELF_FIRST);

            /** @var UniformResourceIterator $uri */
            foreach ($iterator as $uri) {
                $key = $uri->getUrl() . '@010';
                $this->cache[$key] = $uri->getPathname();
            }
        }

        return $this;
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


    protected function findCached($uri, $array, $absolute, $all)
    {
        // Local caching: make sure that the function gets only called at once for each file.
        $key = $uri .'@'. (int) $array . (int) $absolute . (int) $all;

        if (!isset($this->cache[$key])) {
            list ($scheme, $file) = $this->parseResource($uri);

            $this->cache[$key] = $this->find($scheme, $file, $array, $absolute, $all);
        }

        return $this->cache[$key];
    }

    /**
     * @param  string $scheme
     * @param  string $file
     * @param  bool $array
     * @param  bool $absolute
     * @param  bool $all
     *
     * @throws \InvalidArgumentException
     * @return array|string|bool
     * @internal
     */
    protected function find($scheme, $file, $array, $absolute, $all)
    {
        if (!isset($this->schemes[$scheme])) {
            throw new \InvalidArgumentException("Invalid resource {$scheme}://");
        }

        $results = $array ? [] : false;
        foreach ($this->schemes[$scheme] as $prefix => $paths) {
            // If $file doesn't begin with $prefix, move on to the next path
            if ($prefix && strpos($file, $prefix) !== 0) {
                continue;
            }

            // Remove prefix from filename.
            $filename = '/' . trim(substr($file, strlen($prefix)), '\/');

            foreach ($paths as $path) {
                if (is_array($path)) {
                    // Handle scheme lookup, for cases where the path is part of another stream [<stream>, <relativepath>]
                    $relPath = trim($path[1] . $filename, '/');
                    $found = $this->find($path[0], $relPath, $array, $absolute, $all);
                    if ($found) {
                        if (!$array) {
                            return $found;
                        }
                        $results = array_merge($results, $found);
                    }
                } else {
                    // TODO: We could provide some extra information about the path to remove the need for regex-based matching.
                    $isAbsolutePath = $this->getRootDirectory($path);
                    // Check absolute paths for both unix and windows
                    if (!$path || !$isAbsolutePath) {
                        // Handle relative path lookup.
                        $relPath = trim($path . $filename, '/');
                        $fullPath = $this->base . '/' . $relPath;
                        $searchPath = rtrim($this->base . '/' . $path, '/');                        
                    } else {
                        // Handle absolute path lookup.
                        $fullPath = rtrim($path . $filename, '/');
                        $searchPath = rtrim($path, '/');
                        if (!$absolute) {
                            throw new \RuntimeException("UniformResourceLocator: Absolute stream path with relative lookup not allowed ({$prefix})", 500);
                        }
                    }
                    
                    // Resolve $fullPath to its "realpath", i.e. resolving any directory traversals and symbolic links
                    $rootSymbol = $this->getRootDirectory($fullPath);
                    $realFullPath = $rootSymbol . $this->normalizePath($fullPath);
                    
                    // Make sure that $realFullPath begins with $searchPath - otherwise it is not allowed!
                    $allowed = (strpos($realFullPath, $searchPath) === 0);
                    
                    if ($all || ($allowed && file_exists($realFullPath))) {
                        // Decide whether we're returning the absolute or relative path
                        if ($absolute){
                            $current = $realFullPath;
                        } else {
                            $current = $this->normalizePath($relPath);
                        }
                        
                        if (!$array) {
                            return $current;
                        }
                        $results[] = $current;
                    }
                }
            }
        }

        return $results;
    }
    
    /**
     * Get the root directory of an absolute file path in Windows or *nix systems.
     *
     * If the path is not an absolute file path, this will return the empty string.
     * If it matches an absolute path on *nix type systems, this will return a forward slash `/`.
     * If it matches an absolute path on Windows systems, this will return a drive letter, colon, and backslash (e.g. "C:\")
     *
     * @param string $path
     *
     * @return string
     */
    public function getRootDirectory($path)
    {
        if ($path) {
            $isAbsolutePath = preg_match('`^/|\w+:`', $path, $rootSymbol);
            return isset($rootSymbol[0]) ? $rootSymbol[0] : '';
        } else {
            return '';
        }
    }
    
    /**
     * Normalize path.
     *
     * Adapted from thephpleague/flysystem
     * @link https://github.com/thephpleague/flysystem/blob/15dcc8ee9b279b40fbcca47ad9c7257a41b61947/src/Util.php#L74-L121 
     * @param string $path
     *
     * @return string|false
     */
    public function normalizePath($path)
    {
        // Remove any kind of funky unicode whitespace
        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = $this->normalizeRelativePath($normalized);
        if (preg_match('#/\.{2}|^\.{2}/|^\.{2}$#', $normalized)) {
            return false;
        }
        $normalized = preg_replace('#\\\{2,}#', '\\', trim($normalized, '\\'));
        $normalized = preg_replace('#/{2,}#', '/', trim($normalized, '/'));
        return $normalized;
    }
    
    /**
     * Normalize relative directories in a path.
     *
     * Adapted from thephpleague/flysystem
     * @link https://github.com/thephpleague/flysystem/blob/15dcc8ee9b279b40fbcca47ad9c7257a41b61947/src/Util.php#L74-L121 
     * @param string $path
     *
     * @return string
     */
    public function normalizeRelativePath($path)
    {
        // Path remove self referring paths ("/./").
        $path = preg_replace('#/\.(?=/)|^\./|(/|^)\./?$#', '', $path);
        // Regex for resolving relative paths
        $regex = '#/*[^/\.]+/\.\.#Uu';
        while (preg_match($regex, $path)) {
            $path = preg_replace($regex, '', $path);
        }
        return $path;
    }    
}
