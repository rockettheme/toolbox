<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\File\YamlFile;

require_once 'helper.php';

class BlueprintsBlueprintFormTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testLoad($test)
    {
        $blueprint = new Blueprint($test);
        $blueprint->setOverrides(
            ['extends' => ['extends', 'basic']]
        );
        $blueprint->load();

        // Save test results if they do not exist (data needs to be verified by human!)
        $resultFile = YamlFile::instance(__DIR__ . '/data/form/items/' . $test . '.yaml');
        if (!$resultFile->exists()) {
            $resultFile->content(['unverified' => true] + $blueprint->toArray());
            $resultFile->save();
        }

        // Test 1: Loaded form.
        $this->assertEquals($this->loadYaml($test, 'form/items'), $blueprint->toArray());

    }

    public function dataProvider()
    {
        return [
            ['empty'],
            ['basic'],
            ['import'],
            ['extends']
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
