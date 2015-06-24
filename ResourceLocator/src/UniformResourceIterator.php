<?php
namespace RocketTheme\Toolbox\ResourceLocator;

use FilesystemIterator;

/**
 * Implements FilesystemIterator for uniform resource locator.
 *
 * @package RocketTheme\Toolbox\ResourceLocator
 * @author RocketTheme
 * @license MIT
 */
class UniformResourceIterator extends FilesystemIterator
{
    /** @var FilesystemIterator */
    protected $iterator;
    protected $found;
    protected $path;
    protected $stack;

    protected $url;
    protected $flags;
    protected $locator;

    public function __construct($path, $flags = null, UniformResourceLocator $locator = null)
    {
        if (!$locator) {
            throw new \BadMethodCallException('Use $locator->getIterator() instead');
        }

        $this->url = $path;
        $this->setFlags($flags);
        $this->locator = $locator;
        $this->rewind();
    }

    public function current()
    {
        if ($this->flags & static::CURRENT_AS_SELF) {
            return $this;
        }
        return $this->iterator ? $this->iterator->current() : null;
    }

    public function key()
    {
        return $this->iterator ? $this->iterator->key() : null;
    }

    public function next()
    {
        do {
            $found = $this->findNext();
        } while ($found && !empty($this->found[$found]));

        if ($found) {
            // Mark the file as found.
            $this->found[$found] = true;
        }
    }

    protected function findNext()
    {
        if ($this->iterator) {
            $this->iterator->next();
        }
        if (!$this->valid()) {
            $this->iterator = null;
            do {
                // Move to the next iterator if it exists.
                $path = key($this->stack);

                if (!isset($path)) {
                    return null;
                }

                $this->iterator = new \FilesystemIterator($path, $this->getFlags());
                $this->path = array_shift($this->stack);
            } while (!$this->iterator->valid());
        }

        return $this->getFilename();
    }

    public function valid()
    {
        return $this->iterator && $this->iterator->valid();
    }

    public function rewind()
    {
        $this->found = [];
        $this->path = $this->url;
        $this->stack = array_fill_keys($this->locator->findResources($this->url), $this->path);
        $this->next();
    }

    public function getUrl()
    {
        return $this->path;
    }

    public function getUrlname()
    {
        return $this->iterator ? $this->path . '/' . $this->iterator->getFilename() : null;
    }

    public function seek($position)
    {
        throw new \RuntimeException('Seek not implemented');
    }

    public function getATime()
    {
        return $this->iterator ? $this->iterator->getATime() : null;
    }

    public function getBasename($suffix = null)
    {
        return $this->iterator ? $this->iterator->getBasename($suffix) : null;
    }

    public function getCTime()
    {
        return $this->iterator ? $this->iterator->getCTime() : null;
    }

    public function getExtension()
    {
        return $this->iterator ? $this->iterator->getExtension() : null;
    }

    public function getFilename()
    {
        return $this->iterator ? $this->iterator->getFilename() : null;
    }

    public function getGroup()
    {
        return $this->iterator ? $this->iterator->getGroup() : null;
    }

    public function getInode()
    {
        return $this->iterator ? $this->iterator->getInode() : null;
    }

    public function getMTime()
    {
        return $this->iterator ? $this->iterator->getMTime() : null;
    }

    public function getOwner()
    {
        return $this->iterator ? $this->iterator->getOwner() : null;
    }

    public function getPath()
    {
        return $this->iterator ? $this->iterator->getPath() : null;
    }

    public function getPathname()
    {
        return $this->iterator ? $this->iterator->getPathname() : null;
    }

    public function getPerms()
    {
        return $this->iterator ? $this->iterator->getPerms() : null;
    }

    public function getSize()
    {
        return $this->iterator ? $this->iterator->getSize() : null;
    }

    public function getType()
    {
        return $this->iterator ? $this->iterator->getType() : null;
    }

    public function isDir()
    {
        return $this->iterator ? $this->iterator->isDir() : false;
    }

    public function isDot()
    {
        return $this->iterator ? $this->iterator->isDot() : false;
    }

    public function isExecutable()
    {
        return $this->iterator ? $this->iterator->isExecutable() : false;
    }

    public function isFile()
    {
        return $this->iterator ? $this->iterator->isFile() : false;
    }

    public function isLink()
    {
        return $this->iterator ? $this->iterator->isLink() : false;
    }

    public function isReadable()
    {
        return $this->iterator ? $this->iterator->isReadable() : false;
    }

    public function isWritable()
    {
        return $this->iterator ? $this->iterator->isWritable() : false;
    }

    public function __toString()
    {
        return $this->iterator ? $this->iterator->__toString() : '';
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function setFlags($flags = null)
    {
        $this->flags = $flags === null ? static::KEY_AS_PATHNAME | static::CURRENT_AS_FILEINFO | static::SKIP_DOTS : $flags;

        if ($this->iterator) {
            $this->iterator->setFlags($this->flags);
        }
    }
}
