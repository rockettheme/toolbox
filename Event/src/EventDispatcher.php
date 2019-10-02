<?php
namespace RocketTheme\Toolbox\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implements Symfony EventDispatcher interface.
 *
 * @package RocketTheme\Toolbox\Event
 * @author RocketTheme
 * @license MIT
 */
class EventDispatcher extends BaseEventDispatcher implements EventDispatcherInterface
{
    public function dispatch($event/*, string $eventName = null*/)
    {
        if (null === $event) {
            $event = new Event();
        }

        $eventName = 1 < \func_num_args() ? func_get_arg(1) : null;

        return parent::dispatch($event, $eventName);
    }
}
