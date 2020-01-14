<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\Blueprints\BlueprintSchema;
use RocketTheme\Toolbox\File\YamlFile;

require_once 'helper.php';

class BlueprintsBlueprintSchemaTest extends TestCase
{
    public function testCreation()
    {
        $blueprints = new BlueprintSchema;

        $this->assertEquals(
            [
                'items' => [],
                'rules' => [],
                'nested' => [],
                'dynamic' => [],
                'filter' => ['validation' => true]
            ],
            $blueprints->getState());

        $this->assertEquals([], $blueprints->getDefaults());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testLoad($test)
    {
        $blueprint = new Blueprint($test);
        $blueprint->load()->init();

        $schema = $blueprint->schema();

        // Save test results if they do not exist (data needs to be verified by human!)
        $resultFile = YamlFile::instance(__DIR__ . '/data/schema/state/' . $test . '.yaml');
        if (!$resultFile->exists()) {
            $resultFile->content(['unverified' => true] + $schema->getState());
            $resultFile->save();
        }

        // Test 1: Internal state.
        $this->assertEquals($this->loadYaml($test, 'schema/state'), $schema->getState());

        $schema->init();

        // Save test results if they do not exist (data needs to be verified by human!)
        $resultFile = YamlFile::instance(__DIR__ . '/data/schema/init/' . $test . '.yaml');
        if (!$resultFile->exists()) {
            $resultFile->content(['unverified' => true] + $schema->getState());
            $resultFile->save();
        }

        // Test 2: Initialize blueprint.
        $this->assertEquals($this->loadYaml($test, 'schema/init'), $schema->getState());

        // Test 3: Default values.
        $this->assertEquals($this->loadYaml($test, 'schema/defaults'), $schema->getDefaults());

        // Test 4: Extra values.
        $this->assertEquals($this->loadYaml($test, 'schema/extra'), $schema->extra($this->loadYaml($test, 'input')));

        // Test 5: Merge data.
        $this->assertEquals(
            $this->loadYaml($test, 'schema/merge'),
            $schema->mergeData($schema->getDefaults(), $this->loadYaml($test, 'input'))
        );
    }

    public function dataProvider()
    {
        return [
            ['empty'],
            ['basic'],
        ];
    }

    protected function loadYaml($test, $type = 'blueprint')
    {
        $file = YamlFile::instance(__DIR__ . "/data/{$type}/{$test}.yaml");
        $content = $file->content();
        $file->free();

        return $content;
    }
}
