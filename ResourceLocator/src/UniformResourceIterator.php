<?php
namespace RocketTheme\Toolbox\ResourceLocator;

use FilesystemIterator;

/**
 * Implements Read/Write Streams.
 *
 * @package RocketTheme\Toolbox\ResourceLocator
 * @author RocketTheme
 * @license MIT
 */
class UniformResourceIterator extends \FilesystemIterator
{
    /** @var array|FilesystemIterator[] */
    protected $iterators = [];
    protected $index = 0;
    protected $found = [];
    protected $flags;

    public function __construct($paths, $flags = null, UniformResourceLocator $locator = null)
    {
        if (!$locator) {
            throw new \BadMethodCallException('Use $locator->getIterator() instead');
        }

        $this->setFlags($flags);

        foreach ($locator->findResources($paths) as $path) {
            $this->iterators[] = new FilesystemIterator($path, $this->flags);
        }
    }

    public function current()
    {
        if ($this->flags & static::CURRENT_AS_SELF) {
            return $this;
        }
        return $this->iterators[$this->index]->current();
    }

    public function key()
    {
        return $this->iterators[$this->index]->key();
    }

    public function next()
    {
        if (!isset($this->iterators[$this->index])) {
            return;
        }

        do {
            $found = $this->findNext();
        } while ($found && !empty($this->found[$found]));

        if (!$found) {
            return;
        }

        // Mark the file as found.
        $this->found[$found] = true;
    }

    protected function findNext()
    {
        $iterator = $this->iterators[$this->index];

        $iterator->next();
        if (!$iterator->valid()) {
            do {
                $this->index++;
                if (!isset($this->iterators[$this->index])) {
                    return null;
                }
                $iterator = $this->iterators[$this->index];
            } while (!$iterator->valid());
        }

        return $this->getFilename();
    }

    public function valid()
    {
         return isset($this->iterators[$this->index]) && $this->iterators[$this->index]->valid();
    }

    public function rewind()
    {
        $this->index = 0;
        $this->found = [];

        // Rewind all iterators.
        foreach($this->iterators as $iterator) {
            $iterator->setFlags($this->flags);
            $iterator->rewind();
        }

        // Set pointer to the first matching file.
        $iterator = reset($this->iterators);
        if (!$iterator->valid()) {
            // Iterator is empty. Find the next file.
            $this->next();
        } else {
            // Mark the file as found.
            $this->found[$iterator->getFilename()] = true;
        }
    }

    public function seek($position)
    {
        throw new \RuntimeException('Seek not implemented');
    }

    public function getATime()
    {
        return $this->iterators[$this->index]->getATime();
    }

    public function getBasename($suffix = null)
    {
        return $this->iterators[$this->index]->getBasename($suffix);
    }

    public function getCTime()
    {
        return $this->iterators[$this->index]->getCTime();
    }

    public function getExtension()
    {
        return $this->iterators[$this->index]->getExtension();
    }

    public function getFilename()
    {
        return $this->iterators[$this->index]->getFilename();
    }

    public function getGroup()
    {
        return $this->iterators[$this->index]->getGroup();
    }

    public function getInode()
    {
        return $this->iterators[$this->index]->getInode();
    }

    public function getMTime()
    {
        return $this->iterators[$this->index]->getMTime();
    }

    public function getOwner()
    {
        return $this->iterators[$this->index]->getOwner();
    }

    public function getPath()
    {
        return $this->iterators[$this->index]->getPath();
    }

    public function getPathname()
    {
        return $this->iterators[$this->index]->getPathname();
    }

    public function getPerms()
    {
        return $this->iterators[$this->index]->getPerms();
    }

    public function getSize()
    {
        return $this->iterators[$this->index]->getSize();
    }
    public function getType()
    {
        return $this->iterators[$this->index]->getType();
    }

    public function isDir()
    {
        return $this->iterators[$this->index]->isDir();
    }

    public function isDot()
    {
        return $this->iterators[$this->index]->isDot();
    }

    public function isExecutable()
    {
        return $this->iterators[$this->index]->isExecutable();
    }

    public function isFile()
    {
        return $this->iterators[$this->index]->isFile();
    }

    public function isLink()
    {
        return $this->iterators[$this->index]->isLink();
    }

    public function isReadable()
    {
        return $this->iterators[$this->index]->isReadable();
    }

    public function isWritable()
    {
        return $this->iterators[$this->index]->isWritable();
    }

    public function __toString()
    {
        return $this->iterators[$this->index]->__toString();
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function setFlags($flags = null)
    {
        $this->flags = $flags === null ? static::KEY_AS_PATHNAME | static::CURRENT_AS_FILEINFO | static::SKIP_DOTS : $flags;

        foreach($this->iterators as $iterator) {
            $iterator->setFlags($this->flags);
        }
    }
}
