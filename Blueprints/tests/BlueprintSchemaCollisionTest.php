<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\Blueprints\BlueprintSchema;

/**
 * Regression guard for getgrav/grav#4271.
 *
 * Fields are flattened into a single map keyed by name, and containers are
 * flattened alongside the leaves they hold. A tab, section or column named the
 * same as one of its own descendants therefore lands on the same key, and the
 * leaf is the one that carries the data: it is what a posted value validates
 * and filters against. Grav's stock page blueprint hits this exactly, with a
 * `content` markdown field inside a `content` tab.
 */
class BlueprintsBlueprintSchemaCollisionTest extends TestCase
{
    /**
     * @param array $fields
     * @return array
     */
    private function items(array $fields)
    {
        $schema = new BlueprintSchema();
        $schema->embed('', ['form' => ['fields' => $fields]]);

        return $schema->getState()['items'];
    }

    public function testLeafWinsOverContainerOfTheSameName()
    {
        $items = $this->items([
            'tabs' => [
                'type' => 'tabs',
                'fields' => [
                    'content' => [
                        'type' => 'tab',
                        'fields' => [
                            'content' => [
                                'type' => 'markdown',
                                'validate' => ['type' => 'textarea', 'max' => 0],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('markdown', $items['content']['type']);
        $this->assertSame(['type' => 'textarea', 'max' => 0], $items['content']['validate']);
    }

    public function testLeafWinsHoweverDeeplyTheContainerIsNested()
    {
        $items = $this->items([
            'options' => [
                'type' => 'section',
                'fields' => [
                    'inner' => [
                        'type' => 'section',
                        'fields' => [
                            'options' => ['type' => 'array'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('array', $items['options']['type']);
    }

    public function testContainerIsStillRegisteredWhenNothingCollidesWithIt()
    {
        $items = $this->items([
            'tabs' => [
                'type' => 'tabs',
                'fields' => [
                    'content' => [
                        'type' => 'tab',
                        'fields' => [
                            'body' => ['type' => 'markdown'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('tabs', $items['tabs']['type']);
        $this->assertSame('tab', $items['content']['type']);
        $this->assertSame('markdown', $items['body']['type']);
    }

    public function testCollidingContainerIsStillNotDroppedFromTheNestedTree()
    {
        $schema = new BlueprintSchema();
        $schema->embed('', ['form' => ['fields' => [
            'tabs' => [
                'type' => 'tabs',
                'fields' => [
                    'content' => [
                        'type' => 'tab',
                        'fields' => [
                            'content' => ['type' => 'markdown'],
                            'header.title' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
        ]]]);

        $nested = $schema->getState()['nested'];

        // The form is rendered from `nested`, which is keyed by data path and
        // never had the collision. Resolving the leaf must not disturb it.
        $this->assertSame('content', $nested['content']);
        $this->assertSame('header.title', $nested['header']['title']);
    }

    public function testRemovedLeafIsNotResurrectedByTheContainerItCollidesWith()
    {
        $schema = new BlueprintSchema();
        $schema->embed('', ['form' => ['fields' => [
            'content' => [
                'type' => 'tab',
                'fields' => [
                    'content' => ['type' => 'markdown', 'input@' => false],
                ],
            ],
        ]]]);

        // Registering the container up front must not leave the removed leaf
        // reachable: `nested` is what the form renders and what validation
        // resolves a posted value through.
        $this->assertArrayNotHasKey('content', $schema->getState()['nested']);
    }
}
