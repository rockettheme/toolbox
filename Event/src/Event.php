<?php

namespace RocketTheme\Toolbox\Event;

// Compatibility shim for legacy Composer autoload paths during upgrades.

if (!class_exists(__NAMESPACE__ . '\\Event', false)) {
    require_once __DIR__ . '/../../../../../system/src/RocketTheme/Toolbox/Event/Event.php';
}
