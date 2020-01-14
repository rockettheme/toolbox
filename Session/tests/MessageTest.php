<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\Session\Message;

class SessionMessageTest extends TestCase
{

    public function testCreation()
    {
        $message = new Message;
        $this->assertTrue(true);
    }
}
