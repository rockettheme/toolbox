<?php

use RocketTheme\Toolbox\Blueprints\BlueprintSchema;
use RocketTheme\Toolbox\File\YamlFile;

class BlueprintsBlueprintSchemaTest extends PHPUnit_Framework_TestCase
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
        $input = YamlFile::instance(__DIR__ . '/data/test/' . $test . '.yaml')->content();
        $resultFile = YamlFile::instance(__DIR__ . '/data/state/' . $test . '.yaml');

        $blueprints = new BlueprintSchema;
        $blueprints->embed('', $input);

        if (!$resultFile->exists()) {
            $resultFile->content($blueprints->getState());
            $resultFile->save();
        }

        $this->assertEquals($resultFile->content(), $blueprints->getState());
    }

    public function dataProvider()
    {
        return [
            ['empty'],
            ['basic'],
        ];
    }
}
