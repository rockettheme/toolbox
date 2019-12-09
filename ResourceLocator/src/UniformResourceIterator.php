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
    /** @var array */
    protected $found;
    /** @var array */
    protected $stack;
    /** @var string */
    protected $path;
    /** @var int */
    protected $flags;
    /** @var UniformResourceLocator */
    protected $locator;

    public function __construct($path, $flags = null, UniformResourceLocator $locator = null)
    {
        if (!$locator) {
            throw new \BadMethodCallException('Use $locator->getIterator() instead');
        }

        $this->path = $path;
        $this->setFlags($flags);
        $this->locator = $locator;
        $this->rewind();
    }

    /**
     * @return $this|\SplFileInfo|string
     */
    public function current()
    {
        if ($this->flags & static::CURRENT_AS_SELF) {
            return $this;
        }

        return $this->iterator->current();
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->iterator->key();
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

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator && $this->iterator->valid();
    }

    public function rewind()
    {
        $this->found = [];
        $this->stack = $this->locator->findResources($this->path);
        $this->next();
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        $path = $this->path . (substr($this->path, -1, 1) === '/' ? '' : '/');

        return $path . $this->iterator->getFilename();
    }

    /**
     * @param int $position
     */
    public function seek($position)
    {
        throw new \RuntimeException('Seek not implemented');
    }

    /**
     * @return int
     */
    public function getATime()
    {
        return $this->iterator->getATime();
    }

    /**
     * @param string|null $suffix
     * @return string
     */
    public function getBasename($suffix = null)
    {
        return $this->iterator->getBasename($suffix);
    }

    /**
     * @return int
     */
    public function getCTime()
    {
        return $this->iterator->getCTime();
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->iterator->getExtension();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->iterator->getFilename();
    }

    /**
     * @return int
     */
    public function getGroup()
    {
        return $this->iterator->getGroup();
    }

    /**
     * @return int
     */
    public function getInode()
    {
        return $this->iterator->getInode();
    }

    /**
     * @return int
     */
    public function getMTime()
    {
        return $this->iterator->getMTime();
    }

    /**
     * @return int
     */
    public function getOwner()
    {
        return $this->iterator->getOwner();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->iterator->getPath();
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->iterator->getPathname();
    }

    /**
     * @return int
     */
    public function getPerms()
    {
        return $this->iterator->getPerms();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->iterator->getSize();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->iterator->getType();
    }

    /**
     * @return bool
     */
    public function isDir()
    {
        return $this->iterator->isDir();
    }

    /**
     * @return bool
     */
    public function isDot()
    {
        return $this->iterator->isDot();
    }

    /**
     * @return bool
     */
    public function isExecutable()
    {
        return $this->iterator->isExecutable();
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return $this->iterator->isFile();
    }

    /**
     * @return bool
     */
    public function isLink()
    {
        return $this->iterator->isLink();
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return $this->iterator->isReadable();
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->iterator->isWritable();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->iterator;
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param string|null $flags
     */
    public function setFlags($flags = null)
    {
        $this->flags = $flags === null ? static::KEY_AS_PATHNAME | static::CURRENT_AS_SELF | static::SKIP_DOTS : $flags;

        if ($this->iterator) {
            $this->iterator->setFlags($this->flags);
        }
    }

    /**
     * @return string|null
     */
    protected function findNext()
    {
        if ($this->iterator) {
            $this->iterator->next();
        }

        if (!$this->valid()) {
            do {
                // Move to the next iterator if it exists.
                $path = array_shift($this->stack);

                if (!isset($path)) {
                    return null;
                }

                $this->iterator = new \FilesystemIterator($path, $this->getFlags());
            } while (!$this->iterator->valid());
        }

        return $this->getFilename();
    }
}
