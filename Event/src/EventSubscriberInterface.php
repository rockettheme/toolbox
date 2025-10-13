<?php

namespace RocketTheme\Toolbox\Event;

// Compatibility shim for legacy Composer autoload paths during upgrades.

if (!interface_exists(__NAMESPACE__ . '\\EventSubscriberInterface', false)) {
    require_once __DIR__ . '/../../../../../system/src/RocketTheme/Toolbox/Event/EventSubscriberInterface.php';
}
