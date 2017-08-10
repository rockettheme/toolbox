<?php
namespace RocketTheme\Toolbox\Event;

use RocketTheme\Toolbox\ArrayTraits\ArrayAccessWithGetters;
use RocketTheme\Toolbox\ArrayTraits\Constructor;
use RocketTheme\Toolbox\ArrayTraits\Export;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Implements Symfony Event interface.
 *
 * @package RocketTheme\Toolbox\Event
 * @author RocketTheme
 * @license MIT
 */
class Event extends BaseEvent implements \ArrayAccess
{
    use ArrayAccessWithGetters, Constructor, Export;

    /**
     * @var array
     */
    protected $items = array();
}
