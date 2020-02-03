<?php

use RocketTheme\Toolbox\Blueprints\BlueprintForm;
use RocketTheme\Toolbox\Blueprints\BlueprintSchema;
use RocketTheme\Toolbox\File\YamlFile;

function blueprint_data_option_test(array $param = null, $sort = false)
{
    if ($sort) {
        asort($param);
    }

    return $param ?: ['yes' => 'Yes', 'no' => 'No'];
}


class Blueprint extends BlueprintForm
{
    /**
     * @return BlueprintSchema
     */
    public function schema()
    {
        $schema = new BlueprintSchema();
        $schema->embed('', $this->items);

        return $schema;
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function loadFile($filename)
    {
        $file = YamlFile::instance(__DIR__ . "/data/blueprint/{$filename}.yaml");
        $content = $file->content();
        $file->free();

        return $content;
    }

    /**
     * @param string|array $path
     * @param string $context
     * @return array
     */
    protected function getFiles($path, $context = null)
    {
        if (is_string($path)) {
            // Resolve filename.
            if (isset($this->overrides[$path])) {
                $path = $this->overrides[$path];
            } else {
                if ($context === null) {
                    $context = $this->context;
                }
                if ($context && $context[strlen($context)-1] !== '/') {
                    $context .= '/';
                }
                $path = $context . $path;
            }
        }

        return (array)$path;
    }

}