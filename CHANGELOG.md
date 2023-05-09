# v1.6.5
## 05/09/2023

1. [](#bugfix)
    * Fixed another issue with PHP 8.2

# v1.6.4
## 03/24/2023

1. [](#bugfix)
    * Fixed an issue with PHP 8.2 [#36](https://github.com/rockettheme/toolbox/pull/36)

# v1.6.3
## 02/19/2023

1. [](#bugfix)
   * Fixed a bug in ReadOnlyStream that throws deprecated warning PHP 8.2

# v1.6.2
## 06/14/2022

1. [](#bugfix)
    * Removed support for Symfony 5 Event Dispatcher due to compatibility issues [#32](https://github.com/rockettheme/toolbox/issues/32)

# v1.6.1
## 02/08/2022

1. [](#new)
    * Added support for Symfony 5 YAML and Event Dispatcher
1. [](#bugfix)
    * Fixed PHP 5.6 and 7.0 compatibility

# v1.6.0
## 12/15/2021

1. [](#new)
    * Added **PHP 8.1** support
    * If you use `ArrayTraits\Seriazable`, make sure you do not override the methods (use PHP 8 methods instead)

# v1.5.11
## 10/25/2021

1. [](#new)
    * Updated phpstan to v1.0
1. [](#improved)
    * Added `parent@: true` option for blueprints to include rules for parent fields
1. [](#bugfix)
    * Fixed deprecated warnings in PHP 8.1

# v1.5.10
## 09/29/2021

1. [](#improved)
    * Improved `UniformResourceLocator` to support `file://` scheme
1. [](#bugfix)
    * Fixed blueprint merge where second blueprint has non-array field definition
    * Fixed implicit cast from null to string in `UniformResourceLocator`

# v1.5.9
## 04/14/2021

1. [](#bugfix)
    * Fixed regression in default field type settings

# v1.5.8
## 04/12/2021

1. [](#bugfix)
    * Fixed default field type settings not being merged recursively causing default validation rules to get missing

# v1.5.7
## 02/17/2021

1. [](#new)
   * Pass new phpstan level 8 tests
1. [](#bugfix)
   * Fixed `Trying to access array offset on value of type int` in `BlueprintSchema::getFieldKey()`

# v1.5.6
## 12/03/2020

1. [](#bugfix)
    * Fixed incompatible `File` class

# v1.5.5
## 12/01/2020

1. [](#bugfix)
    * Fixed a PHP8 issue in YAML library

# v1.5.4
## 11/27/2020

1. [](#new)
    * Added PHP 8.0 support
1. [](#bugfix)
    * Fixed `UniformResourceLocator::addPath()` with PHP 8

# v1.5.3
## 11/23/2020

1. [](#bugfix)
    * Fixed `UniformResourceLocator::addPath()` not working properly if override is a stream

# v1.5.2
## 05/18/2020

1. [](#improved)
    * Support symlinks when saving `File` (#30, thanks @schliflo)

# v1.5.1
## 03/19/2020

1. [](#bugfix)
    * Fixed static method call from Blueprints

# v1.5.0
## 02/03/2020

1. [](#new)
    * Updated minimum requirement to PHP 5.6.0
    * Deprecated Event classes in favor of PSR-14
    * PHP 7.4 compatibility: implemented required `Stream::stream_set_option()` method (#28, thanks @lcharette)
    * Pass phpstan level 8 tests
    * Added new `UniformResourceLocator::getResource()` method to simplify code where filename is always required
    * Added support for `replace-name@` in blueprints (#24, thanks @drzraf)
    * Calling `File::instance()` with empty filename is now deprecated
1. [](#bugfix)
    * Fixed `new UniformResourceItarator()` not throwing exception when path is non-existing
    * Fixed missing frontmatter if markdown file had UTF-8 BOM (#14, thanks @A----)
    * Fixed many other edge cases

# v1.4.6
## 03/20/2019

1. [](#bugfix)
    * Fixed `File::writable()` returning true if an existing file is read-only with the folder being writable
    * Fixed `File::save()` silently ignoring failures with read only streams
    * Regression: Fixed file saving when temporary file cannot be created to the current folder / stream

# v1.4.5
## 02/28/2019

1. [](#bugfix)
    * Regression: Fixed undefined variable in `BlueprintSchema`

# v1.4.4
## 02/28/2019

1. [](#bugfix)
    * Regression: Fixed issue with directory creation when saving non-existing file

# v1.4.3
## 02/26/2019

1. [](#improved)
    * Minor code optimizations
    * Improved `File::save()` to use a temporary file if file isn't locked
1. [](#bugfix)
    * Fixed `Obtaining write lock failed on file...`
    * Fixed `mkdir(...)` race condition

# v1.4.2
## 08/08/2018

1. [](#new)
    * Added `UniformResourceLocator::clearCache()` to allow resource cache to be cleared
    * Added `$extends` parameter to `BlueprintForm::load()` to override `extends@`
1. [](#improved)
    * Improved messages in `Stream` exceptions
1. [](#bugfix)
    * Fixed bugs when using `mkdir()`, `rmdir()`, `rename()` or creating new files with URIs

# v1.4.1
## 06/20/2018

1. [](#bugfix)
    * Fixed a bug in blueprint extend and embed

# v1.4.0
## 06/13/2018

1. [](#new)
    * `BlueprintForm`: Implemented support for multiple `import@`s and partial `import@`s (#17)
1. [](#improved)
    * `YamlFile`: Added support for `@data` without quoting it (fixes issues with Symfony 3.4 if `compat=true`)
    * `YamlFile`: Added compatibility mode which falls back to Symfony YAML 2.8.38 if parsing with newer version fails
    * `YamlFile`: Make `compat` and `native` settings global, enable `native` setting by default
    * General code cleanup, some optimizations
1. [](#bugfix)
    * `Session`: Removed broken request counter

# v1.3.9
## 10/08/2017

1. [](#improved)
    * Modified `MarkdownFile::encode()` to dump header with 20 levels of indention (was 5)

# v1.3.8
## 09/23/2017

1. [](#bugfix)
    * Fixed bad PHP docblock that was breaking API generation

# v1.3.7
## 08/28/2017

1. [](#bugfix)
    * Fixed `Event` backwards compatibility by removing getters support

# v1.3.6
## 08/16/2017

1. [](#improved)
    * Improved Event class to support getters and export

# v1.3.5
## 05/22/2017

1. [](#improved)
    * Improved exception message in `File::content()` class when failing to load the data
1. [](#bugfix)
    * Fixed `Blueprintform::resolve()` to use slash notation by default instead of dot notation
    * Fixed warning if badly formatted `$path` parameter is given to `UniformResourceLocator::addPath()`
    * Fixed `Blueprintform::fields()` returning bad value if there were no fields

# v1.3.4
## 05/15/2017

1. [](#new)
    * Blueprint: Add support for a single array field in forms
1. [](#bugfix)
    * Fixed `IniFile::content()` should not fail if file doesn't exist
    * Session: Protection against invalid session cookie name throwing exception
    * Session: Do not destroy session on CLI
    * BlueprintSchema: Fixed warning when field list is not what was expected

# v1.3.3
## 10/06/2016

1. [](#improved)
    * Allow calls without parameter in `UniformResourceLocator::getPaths()`
    * Add support for `BlueprintSchema::getPropertyName()` and `getProperty()`
    * Add domain parameter to Session constructor
    * Add support for `FilesystemIterator::FOLLOW_SYMLINKS` in RecursiveUniformResourceIterator class

# v1.3.2
## 05/24/2016

1. [](#new)
    * Added a new function BlueprintForm::getFilename()
1. [](#bugfix)
    * BlueprintsForm: Detect if user really meant to extend parent blueprint, not another one

# v1.3.1
## 04/25/2016

1. [](#new)
    * Add new function File::rename()
    * Add new function UniformResourceLocator::fillCache()
1. [](#bugfix)
    * Fix collections support in BluprintSchema::extra()
    * Fix exception in stream wrapper when scheme is not defined in locator
    * Prevent UniformResourceLocator from resolving paths outside of defined scheme paths (#8)
    * Fix breaking YAML files which start with three dashes (#5)

# v1.3.0
## 03/07/2016

1. [](#new)
    * Add new function UniformResourceLocator::isStream()
    * Add new class BlueprintForm
    * Renamed Blueprints class into BlueprintSchema
    * Add new function BlueprintSchema::extra() to return data fields which haven't been defined in blueprints
    * Add support to unset and replace blueprint fields and properties
    * Allow arbitrary dynamic fields in Blueprints (property@)
    * Add default properties support for form field types
    * Remove dependency on ircmaxell/password-compat
    * Add support for Symfony 3
    * Add a few unit tests
1. [](#improved)
    * UniformResourceLocator::addPath(): Add option to add path after existing one (falls back to be last if path is not found)
1. [](#bugfix)
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

1. [](#bugfix)
    * Fix regression: Default values for collections were broken
    * Fix Argument 1 passed to `RocketTheme\Toolbox\Blueprints\Blueprints::mergeArrays()` must be of the type array
    * Add exception on Blueprint collection merging; only overridden value should be used
    * File locking truncates contents of the file
    * Stop duplicate Messages getting added to the queue

# v1.1.2
## 08/27/2015

1. [](#new)
    * Creation of Changelog
