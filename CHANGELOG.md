# v1.3.0
## XX/XX/2016

1. [](#new)
    * Renamed Blueprints class into BlueprintSchema
    * Add new function BlueprintSchema::extra() to return data fields which haven't been defined in blueprints
    * Allow arbitrary dynamic fields in BlueprintSchema (property@)
    * Add default properties support for form field types
    * Add new class BlueprintForm
    * Add new function UniformResourceLocator::isStream()
    * Remove dependency on ircmaxell/password-compat
    * Add support for Symfony 3
    * Add a few unit tests
2. [](#improved)
    * UniformResourceLocator::addPath(): Add option to add path after existing one (falls back to be last if path is not found)
3. [](#bugfix)
    * Fix blueprint without a form
    * Fix merging data with empty blueprint

# v1.2.0
## 10/24/2015

1. [](#new)
    * **Backwards compatibility break**: Blueprints class needs to be initialized with `init()` if blueprints contain `@data-*` fields 
    * Renamed NestedArrayAccess::remove() into NestedArrayAccess::undef() to avoid name clashes

# v1.1.4
## 10/15/2015

1. [](#new)
    * Add support for native YAML parsing option to Markdown and YAML file classes

# v1.1.3
## 09/14/2015

3. [](#bugfix)
    * Fix regression: Default values for collections were broken
    * Fix Argument 1 passed to `RocketTheme\Toolbox\Blueprints\Blueprints::mergeArrays()` must be of the type array
    * Add exception on Blueprint collection merging; only overridden value should be used
    * File locking truncates contents of the file
    * Stop duplicate Messages getting added to the queue

# v1.1.2
## 08/27/2015

1. [](#new)
    * Creation of Changelog
