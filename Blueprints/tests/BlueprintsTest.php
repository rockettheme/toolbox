<?php

use RocketTheme\Toolbox\Blueprints\Blueprints;

class BlueprintsBlueprintsTest extends PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $blueprints = new Blueprints;

        $this->assertEquals(
            [
                'items' => [],
                'rules' => [],
                'nested' => [],
                'form' => [],
                'dynamic' => [],
                'filter' => ['validation' => true]
            ],
            $blueprints->getState());

        $this->assertEquals([], $blueprints->getDefaults());
    }
}
