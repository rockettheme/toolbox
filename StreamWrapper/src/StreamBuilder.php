<?php
namespace RocketTheme\Toolbox\StreamWrapper;

class StreamBuilder
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $scheme => $handler) {
            $this->add($scheme, $handler);
        }
    }

    public function add($scheme, $handler)
    {
        if (isset($this->items[$scheme])) {
            if ($handler == $this->items[$scheme]) {
                return;
            }
            throw new \InvalidArgumentException("Stream '{$handler}' has already been initialized.");
        }

        if (!is_a('StreamInterface', $handler, true)) {
            throw new \InvalidArgumentException("Stream '{$handler}' has unknown or invalid type.");
        }

        if (!stream_wrapper_register($scheme, $handler)) {
            throw new \InvalidArgumentException("Stream '{$handler}' could not be initialized.");
        }
        $this->items[$scheme] = $handler;
    }

    public function remove($scheme)
    {
        if (isset($this->items[$scheme])) {
            stream_wrapper_unregister($scheme);
            unset($this->items[$scheme]);
        }
    }

    public function getStreams()
    {
        return $this->items;
    }

    public function isStream($scheme)
    {
        return isset($this->items[$scheme]);
    }

    public function getStreamType($scheme)
    {
        return isset($this->items[$scheme]) ? $this->items[$scheme] : null;
    }
}
