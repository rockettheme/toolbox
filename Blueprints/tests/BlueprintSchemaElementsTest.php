<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\Blueprints\BlueprintSchema;

/**
 * Regression guard for getgrav/grav#4337.
 *
 * An `element` is one branch of the `elements` field above it, and its children
 * are part of the same data as that field. Admin names it
 * `parent_field(elementsName) ~ '.' ~ elementKey`, so the element sits in the
 * container of its `elements` field. It used to be filed at the blueprint root,
 * which cut the children of a list item out of the list.
 */
class BlueprintsBlueprintSchemaElementsTest extends TestCase
{
    /**
     * @param array $fields
     * @param array $types
     * @return BlueprintSchema
     */
    private function schema(array $fields, array $types = [])
    {
        $schema = new BlueprintSchema();
        $schema->setTypes($types);
        $schema->embed('', ['form' => ['fields' => $fields]]);

        return $schema;
    }

    private function listOfElements()
    {
        return [
            'header.sections' => [
                'type' => 'list',
                'fields' => [
                    '.type' => [
                        'type' => 'elements',
                        'fields' => [
                            'text' => ['type' => 'element', 'fields' => ['.heading' => ['type' => 'text']]],
                            'quote' => ['type' => 'element', 'fields' => ['.quote' => ['type' => 'textarea']]],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testElementChildrenAreFiledBesideTheElementsField()
    {
        $items = $this->schema($this->listOfElements(), ['list' => ['array' => true]])->getState()['items'];

        $this->assertSame('text', $items['header.sections.*.text.heading']['type']);
        $this->assertSame('textarea', $items['header.sections.*.quote.quote']['type']);
        $this->assertArrayNotHasKey('text.heading', $items);
        $this->assertArrayNotHasKey('quote.quote', $items);
    }

    public function testListItemHasARuleWhenElementsIsItsOnlyField()
    {
        $schema = $this->schema($this->listOfElements(), ['list' => ['array' => true]]);

        $property = $schema->getProperty('header.sections');

        $this->assertIsArray($property['fields']['*']);
        $this->assertSame('elements', $property['fields']['*']['fields']['type']['type']);
    }

    public function testElementInsideAContainerKeepsTheContainerOfItsElementsField()
    {
        $items = $this->schema([
            'header.demo.type' => [
                'type' => 'elements',
                'fields' => [
                    'gelato' => ['type' => 'element', 'fields' => ['.flavours' => ['type' => 'text']]],
                ],
            ],
        ])->getState()['items'];

        $this->assertArrayHasKey('header.demo.gelato.flavours', $items);
    }

    public function testElementChildrenWithAbsoluteNamesKeepThem()
    {
        $items = $this->schema([
            'basic_captcha.type' => [
                'type' => 'elements',
                'fields' => [
                    'math' => ['type' => 'element', 'fields' => ['basic_captcha.math.min' => ['type' => 'number']]],
                ],
            ],
        ])->getState()['items'];

        $this->assertSame('number', $items['basic_captcha.math.min']['type']);
    }

    public function testElementsFieldWithoutAContainerIsUnchanged()
    {
        $items = $this->schema([
            'mode' => [
                'type' => 'elements',
                'fields' => [
                    'fast' => ['type' => 'element', 'fields' => ['speed' => ['type' => 'text']]],
                ],
            ],
        ])->getState()['items'];

        $this->assertArrayHasKey('fast', $items);
        $this->assertSame('text', $items['speed']['type']);
    }
}
