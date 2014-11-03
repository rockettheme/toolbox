<?php
namespace RocketTheme\Toolbox\ArrayTraits;

/**
 * Implements nested ArrayAccess interface with dot notation.
 *
 * @package RocketTheme\Toolbox\ArrayTraits
 * @author RocketTheme
 * @license MIT
 *
 * @property array $items
 */
trait NestedArrayAccess
{
    /**
     * Get value by using dot notation for nested arrays/objects.
     *
     * @example $value = $this->get('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     * @return mixed  Value.
     */
    public function get($name, $default = null, $separator = '.')
    {
        $path = explode($separator, $name);
        $current = $this->items;
        foreach ($path as $field) {
            if (is_object($current) && isset($current->{$field})) {
                $current = $current->{$field};
            } elseif (is_array($current) && isset($current[$field])) {
                $current = $current[$field];
            } else {
                return $default;
            }
        }

        return $current;
    }

    /**
     * Sey value by using dot notation for nested arrays/objects.
     *
     * @example $value = $this->set('this.is.my.nested.variable', $value);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     */
    public function set($name, $value, $separator = '.')
    {
        $path = explode($separator, $name);
        $current = &$this->items;
        foreach ($path as $field) {
            if (is_object($current)) {
                // Handle objects.
                if (!isset($current->{$field})) {
                    $current->{$field} = array();
                }
                $current = &$current->{$field};
            } else {
                // Handle arrays and scalars.
                if (!is_array($current)) {
                    $current = array($field => array());
                } elseif (!isset($current[$field])) {
                    $current[$field] = array();
                }
                $current = &$current[$field];
            }
        }

        $current = $value;
    }

    /**
     * Set default value by using dot notation for nested arrays/objects.
     *
     * @example $data->def('this.is.my.nested.variable', 'default');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     */
    public function def($name, $default = null, $separator = '.')
    {
        $this->set($name, $this->get($name, $default, $separator), $separator);
    }

    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset  An offset to check for.
     * @return bool          Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        return $this->get($offset) !== null;
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset  The offset to retrieve.
     * @return mixed         Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset  The offset to assign the value to.
     * @param mixed $value   The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * @param $offset
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('unset() not supported in this class.');
    }
}
