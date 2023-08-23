<?php

namespace RocketTheme\Toolbox\Event;

use RocketTheme\Toolbox\ArrayTraits\ArrayAccess;
use RocketTheme\Toolbox\ArrayTraits\Constructor;
use RocketTheme\Toolbox\ArrayTraits\Export;


use Psr\EventDispatcher\StoppableEventInterface;

if (interface_exists(StoppableEventInterface::class)) {
    /**
     * Implements Symfony Event interface.
     *
     * @package RocketTheme\Toolbox\Event
     * @author RocketTheme
     * @license MIT
     */
    class Event implements \ArrayAccess
    {
        use ArrayAccess, Constructor, Export;

        private $propagationStopped = false;

        /** @var array */
        protected $items = [];

        public function isPropagationStopped()
        {
            return $this->propagationStopped;
        }

        public function stopPropagation()
        {
            $this->propagationStopped = true;
        }
    }
} else {
    /**
     * Implements Symfony Event interface.
     *
     * @package RocketTheme\Toolbox\Event
     * @author RocketTheme
     * @license MIT
     */
    class Event implements \ArrayAccess
    {
        use ArrayAccess, Constructor, Export;

        private $propagationStopped = false;

        /** @var array */
        protected $items = [];

        /**
         * @return bool Whether propagation was already stopped for this event
         */
        public function isPropagationStopped()
        {
            return $this->propagationStopped;
        }

        public function stopPropagation()
        {
            $this->propagationStopped = true;
        }
    }
}
