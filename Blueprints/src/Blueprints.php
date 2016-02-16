<?php
namespace RocketTheme\Toolbox\Blueprints;

/**
 * Blueprints can be used to define a data structure.
 *
 * @package RocketTheme\Toolbox\Blueprints
 * @author RocketTheme
 * @license MIT
 */
class Blueprints
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $nested = [];

    /**
     * @var array
     */
    protected $form = [];

    /**
     * @var array
     */
    protected $dynamic = [];

    /**
     * @var array
     */
    protected $filter = ['validation' => true];

    /**
     * @var array
     */
    protected $types = [];

    /**
     * Constructor.
     *
     * @param array $serialized  Serialized content if available.
     */
    public function __construct($serialized = null)
    {
        if (is_array($serialized) && !empty($serialized)) {
            $this->items = (array) $serialized['items'];
            $this->rules = (array) $serialized['rules'];
            $this->nested = (array) $serialized['nested'];
            $this->form = (array) $serialized['form'];
            $this->dynamic = (array) $serialized['dynamic'];
            $this->filter = (array) $serialized['filter'];
        }
    }

    /**
     * @param array $types
     * @return $this
     */
    public function setTypes(array $types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Restore Blueprints object.
     *
     * @param array $serialized
     * @return static
     */
    public static function restore(array $serialized)
    {
        return new static($serialized);
    }

    /**
     * Initialize blueprints with its dynamic fields.
     *
     * @param string $name
     * @return $this
     */
    public function init($name = 'dynamic')
    {
        foreach ($this->dynamic as $key => $data) {
            $field = &$this->items[$key];
            foreach ($data as $property => $call) {
                $action = $name . ucfirst(isset($call['action']) ? $call['action'] : 'data');

                if (method_exists($this, $action)) {
                    $this->{$action}($field, $property, $call);
                }
            }
        }

        return $this;
    }

    /**
     * Set filter for inherited properties.
     *
     * @param array $filter     List of field names to be inherited.
     */
    public function setFilter(array $filter)
    {
        $this->filter = array_flip($filter);
    }

    /**
     * Get value by using dot notation for nested arrays/objects.
     *
     * @example $value = $data->get('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     *
     * @return mixed  Value.
     */
    public function get($name, $default = null, $separator = '.')
    {
        $name = $separator != '.' ? strtr($name, $separator, '.') : $name;

        return isset($this->items[$name]) ? $this->items[$name] : $default;
    }

    /**
     * Set value by using dot notation for nested arrays/objects.
     *
     * @example $value = $data->set('this.is.my.nested.variable', $newField);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     */
    public function set($name, $value, $separator = '.')
    {
        $name = $separator != '.' ? strtr($name, $separator, '.') : $name;

        $this->items[$name] = $value;
        $this->addProperty($name);
    }

    /**
     * Define value by using dot notation for nested arrays/objects.
     *
     * @example $value = $data->set('this.is.my.nested.variable', true);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     */
    public function def($name, $value, $separator = '.')
    {
        $this->set($name, $this->get($name, $value, $separator), $separator);
    }

    /**
     * @return array
     * @deprecated
     */
    public function toArray()
    {
        return $this->getState();
    }

    /**
     * Convert object into an array.
     *
     * @return array
     */
    public function getState()
    {
        return [
            'items' => $this->items,
            'rules' => $this->rules,
            'nested' => $this->nested,
            'form' => $this->form,
            'dynamic' => $this->dynamic,
            'filter' => $this->filter
        ];
    }

    /**
     * Get nested structure containing default values defined in the blueprints.
     *
     * Fields without default value are ignored in the list.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->buildDefaults($this->nested);
    }

    /**
     * Embed an array to the blueprint.
     *
     * @param $name
     * @param array $value
     * @param string $separator
     * @param int $strategy     Merge strategy -1 = merge parent, 0 = replace, 1 = merge child.
     * @return $this
     */
    public function embed($name, array $value, $separator = '.', $strategy = 0)
    {
        if (isset($value['rules'])) {
            $this->rules = array_merge($this->rules, $value['rules']);
        }
        if (!isset($value['form']['fields']) || !is_array($value['form']['fields'])) {
            $value['form']['fields'] = [];
        }
        $name = $separator != '.' ? strtr($name, $separator, '.') : $name;

        $meta = array_diff_key($value, ['form' => 1]);
        $form = array_diff_key($value['form'], ['fields' => 1]);
        $items = isset($this->items[$name]) ? $this->items[$name] : ['type' => '_root', 'form_field' => false];

        if ($strategy && isset($this->items[$name]['meta'])) {
            if ($strategy < 0) {
                $meta = $this->items[$name]['meta'] + $meta;
                $form = $this->items[$name]['form'] + $form;
            } else {
                $meta += $this->items[$name]['meta'];
                $form += $this->items[$name]['form'];
            }
        }

        $this->items[$name] = [
                'meta' => $meta,
                'form' => $form
            ] + $items;

        $this->addProperty($name);

        $prefix = $name ? $name . '.' : '';
        $params = array_intersect_key($form, $this->filter);
        $location = [$name];
        $fields = $this->parseFormFields($value['form']['fields'], $params, $prefix, '', $strategy, $location);

        $this->setFormFields($location, $fields);

        return $this;
    }

    /**
     * Merge two arrays by using blueprints.
     *
     * @param  array $data1
     * @param  array $data2
     * @param  string $name         Optional
     * @param  string $separator    Optional
     * @return array
     */
    public function mergeData(array $data1, array $data2, $name = null, $separator = '.')
    {
        $nested = $this->getProperty($name, $separator);

        return $this->mergeArrays($data1, $data2, $nested);
    }


    /**
     * Get property from the definition.
     *
     * @param  string  $path  Comma separated path to the property.
     * @param  string  $separator
     * @return array
     * @internal
     */
    public function getProperty($path = null, $separator = '.')
    {
        if (!$path) {
            return $this->nested;
        }
        $parts = explode($separator, $path);
        $item = array_pop($parts);

        $nested = $this->nested;
        foreach ($parts as $part) {
            if (!isset($nested[$part])) {
                return [];
            }
            $nested = $nested[$part];
        }

        return isset($nested[$item]) && is_array($nested[$item]) ? $nested[$item] : [];
    }

    /**
     * Return data fields that do not exist in blueprints.
     *
     * @param  array  $data
     * @param  string $prefix
     * @return array
     */
    public function extra(array $data, $prefix = '')
    {
        $rules = &$this->nested;

        // Drill down to prefix level
        if (!empty($prefix)) {
            $parts = explode('.', trim($prefix, '.'));
            foreach ($parts as $part) {
                $rules = isset($rules[$part]) ? $rules[$part] : [];
            }
        }

        return $this->extraArray($data, $rules, $prefix);
    }

    /**
     * @param array $nested
     * @return array
     */
    protected function buildDefaults(array &$nested)
    {
        $defaults = [];

        foreach ($nested as $key => $value) {
            if ($key === '*') {
                // TODO: Add support for adding defaults to collections.
                continue;
            }
            if (is_array($value)) {
                // Recursively fetch the items.
                $list = $this->buildDefaults($value);

                // Only return defaults if there are any.
                if (!empty($list)) {
                    $defaults[$key] = $list;
                }
            } else {
                // We hit a field; get default from it if it exists.
                $item = $this->get($value);

                // Only return default value if it exists.
                if (isset($item['default'])) {
                    $defaults[$key] = $item['default'];
                }
            }
        }

        return $defaults;
    }

    /**
     * @param array $data1
     * @param array $data2
     * @param array $rules
     * @return array
     * @internal
     */
    protected function mergeArrays(array $data1, array $data2, array $rules)
    {
        foreach ($data2 as $key => $field) {
            $val = isset($rules[$key]) ? $rules[$key] : null;
            $rule = is_string($val) ? $this->items[$val] : null;

            if (!empty($rule['type']) && $rule['type'][0] === '_'
                || (array_key_exists($key, $data1) && is_array($data1[$key]) && is_array($field) && is_array($val) && !isset($val['*']))
            ) {
                // Array has been defined in blueprints and is not a collection of items.
                $data1[$key] = $this->mergeArrays($data1[$key], $field, $val);
            } else {
                // Otherwise just take value from the data2.
                $data1[$key] = $field;
            }
        }

        return $data1;
    }

    /**
     * Gets all field definitions from the blueprints.
     *
     * @param array $fields     Fields to parse.
     * @param array $params     Property parameters.
     * @param string $prefix    Property prefix.
     * @param string $parent    Parent property.
     * @param int $strategy     Merge strategy -1 = merge parent, 0 = replace, 1 = merge child.
     * @param array $formPath
     * @return array
     */
    protected function parseFormFields(array &$fields, array $params, $prefix = '', $parent = '', $strategy = 0, array $formPath = [])
    {
        $formOld = $this->getFormFields($formPath);
        $formNew = [];

        // Go though all the fields in current level.
        foreach ($fields as $key => &$field) {
            // Set name from the array key.
            if ($key && $key[0] == '.') {
                $key = ($parent ?: rtrim($prefix, '.')) . $key;
            } else {
                $key = $prefix . $key;
            }

            $newPath = array_merge($formPath, [$key]);

            $properties = array_diff_key($field, ['fields' => 1]) + $params;
            $properties['name'] = $key;

            // Set default properties for the field type.
            $type = isset($properties['type']) ? $properties['type'] : '';
            if (isset($this->types[$type])) {
                $properties += $this->types[$type];
            }

            // If strategy, use either merge parent strategy or merge child strategy.
            if ($strategy && isset($this->items[$key])) {
                $properties = $strategy < 0 ? $this->items[$key] + $properties : $properties + $this->items[$key];
            }

            if (!isset($this->items[$key]) && (!isset($properties['form_field']) || $properties['form_field'])) {
                // Add missing property.
                $this->addProperty($key);
            }

            if (isset($field['fields'])) {
                // Recursively get all the nested fields.
                $isArray = !empty($properties['array']);
                $newParams = array_intersect_key($properties, $this->filter);
                $formNew[$key] = $this->parseFormFields($field['fields'], $newParams, $prefix, $key . ($isArray ? '.*': ''), $strategy, $newPath);
            } else {
                if (!isset($this->items[$key])) {
                    // Add parent rules.
                    $path = explode('.', $key);
                    array_pop($path);
                    $parent = '';
                    foreach ($path as $part) {
                        $parent .= ($parent ? '.' : '') . $part;
                        if (!isset($this->items[$parent])) {
                            $this->items[$parent] = ['type' => '_parent', 'name' => $parent, 'form_field' => false];
                        }
                    }
                }

                $this->parseProperties($key, $properties, $newPath);

                $formNew[$key] = [];
            }

            $this->items[$key] = $properties;
        }

        if ($strategy > 0) {
            return $this->reorder($formOld + $formNew);
        } else {
            return $this->reorder($formNew + $formOld);
        }
    }

    protected function reorder(array $items)
    {
        $modified = false;
        $reordered = $list = array_keys($items);
        foreach ($list as $item) {
            $property = $this->items[$item];
            if (isset($property['ordering'])) {
                $modified = true;
                if (is_scalar($property['ordering']) && (string)(int) $property['ordering'] === (string) $property['ordering']) {
                    $location = array_search($item, $reordered);
                    $rel = array_splice($reordered, $location, 1);
                    $location = (int) $property['ordering'];
                    array_splice($reordered, $location, 0, $rel);
                } elseif (isset($items[$property['ordering']])) {
                    $location = array_search($item, $reordered);
                    $rel = array_splice($reordered, $location, 1);
                    $location = array_search($property['ordering'], $reordered);
                    array_splice($reordered, $location + 1, 0, $rel);
                }
            }
        }

        return $modified ? array_merge(array_flip($reordered), $items) : $items;
    }

    protected function parseProperties($key, array &$properties, array $formPath)
    {
        if (!empty($properties['data'])) {
            $this->dynamic[$key] = $properties['data'];
        }

        foreach ($properties as $name => $value) {
            if (!empty($name) && ($name[0] === '@' || $name[strlen($name) - 1] === '@')) {
                $list = explode('-', trim($name, '@'), 2);
                $action = array_shift($list);
                $property = array_shift($list);

                if ($action === 'data') {
                    if (is_array($value)) {
                        $func = array_shift($value);
                    } else {
                        $func = $value;
                        $value = array();
                    }

                    $this->dynamic[$key][$property] = ['function' => $func, 'params' => $value, 'form' => $formPath];
                } else {
                    $this->dynamic[$key][$property] = ['action' => $action, 'params' => $value, 'form' => $formPath];
                }
            }
        }

        // Initialize predefined validation rule.
        if (isset($properties['validate']['rule'])) {
            $properties['validate'] += $this->getRule($properties['validate']['rule']);
        }
    }

    /**
     * Add property to the definition.
     *
     * @param  string  $path  Comma separated path to the property.
     * @internal
     */
    protected function addProperty($path)
    {
        $parts = explode('.', $path);
        $item = array_pop($parts);

        $nested = &$this->nested;
        foreach ($parts as $part) {
            if (!isset($nested[$part]) || !is_array($nested[$part])) {
                $nested[$part] = [];
            }
            $nested = &$nested[$part];
        }

        if (!isset($nested[$item])) {
            $nested[$item] = $path;
        }
    }

    /**
     * @param array $parts
     * @return array
     */
    protected function getFormFields(array &$parts)
    {
        $nested = &$this->form;
        foreach ($parts as $part) {
            if (!isset($nested[$part])) {
                return [];
            }
            $nested = &$nested[$part];
        }

        return $nested;
    }

    /**
     * @param array $parts
     * @param array $fields
     */
    protected function setFormFields(array &$parts, array $fields)
    {
        $nested = &$this->form;
        foreach ($parts as $part) {
            if (!isset($nested[$part]) || !is_array($nested[$part])) {
                $nested[$part] = [];
            }
            $nested = &$nested[$part];
        }

        $nested = $fields;
    }

    /**
     * @param $rule
     * @return array
     * @internal
     */
    protected function getRule($rule)
    {
        if (isset($this->rules[$rule]) && is_array($this->rules[$rule])) {
            return $this->rules[$rule];
        }
        return [];
    }

    /**
     * @param array $data
     * @param array $rules
     * @param string $prefix
     * @return array
     * @internal
     */
    protected function extraArray(array $data, array $rules, $prefix)
    {
        $array = [];
        foreach ($data as $key => $field) {
            $val = isset($rules[$key]) ? $rules[$key] : null;
            $rule = is_string($val) ? $this->items[$val] : null;

            if ($rule) {
                // Item has been defined in blueprints.
            } elseif (is_array($field) && is_array($val)) {
                // Array has been defined in blueprints.
                $array += $this->ExtraArray($field, $val, $prefix . $key . '.');
            } else {
                // Undefined/extra item.
                $array[$prefix.$key] = $field;
            }
        }
        return $array;
    }

    /**
     * @param array $field
     * @param string $property
     * @param array $call
     */
    protected function dynamicData(array &$field, $property, array &$call)
    {
        $function = $call['function'];
        $params = $call['params'];

        list($o, $f) = preg_split('/::/', $function, 2);
        if (!$f) {
            if (function_exists($o)) {
                $data = call_user_func_array($o, $params);
            }
        } else {
            if (method_exists($o, $f)) {
                $data = call_user_func_array(array($o, $f), $params);
            }
        }

        // If function returns a value,
        if (isset($data)) {
            if (isset($field[$property]) && is_array($field[$property]) && is_array($data)) {
                // Combine field and @data-field together.
                $field[$property] += $data;
            } else {
                // Or create/replace field with @data-field.
                $field[$property] = $data;
            }
        }
    }
}
