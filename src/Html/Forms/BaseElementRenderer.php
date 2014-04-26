<?php namespace Iyoworks\Html\Forms;

use Illuminate\View\Environment;
use Iyoworks\Html\FormBuilder;
use Iyoworks\Html\HtmlBuilder;
use Iyoworks\Support\Str;

abstract class BaseElementRenderer implements ElementRendererInterface  {

    public function getInputFieldOptions($field, $list, $selected = null)
    {
        $selected = (array) $selected;
        $list = (array) $list;
        $html = array();
        $attributes = ['name' => $field->name, 'type' => $field->type];
        foreach ($list as $value => $display)
        {
            $_option = $this->inputOption($value, $selected, $attributes);
            $format = $field->getProperty('checkable-format');
            $str = str_replace(['[label]', '[name]', '[checkable]'], [$display, $field->name, $_option], $format);
            $html[] = $str;
        }
        return implode('', $html);
    }

    /**
     * Create a select element option.
     *
     * @param  string $value
     * @param  string $selected
     * @param array $attributes
     * @return string
     */
    protected function inputOption($value, $selected, array $attributes = [])
    {
        $selected = $this->getSelectedValue($value, $selected, 'checked');
        $options = array_merge($attributes, array('value' => $this->e($value), 'checked' => $selected));
        $atts = $this->makeAttributeString($options);
        return "<input{$atts}>";
    }

    /**
     * @param $field
     * @param $list
     * @param null $selected
     * @return string
     */
    public function getSelectFieldOptions($field, $list, $selected = null)
    {
        $selected = (array) $selected;
        $list = (array) $list;
        $html = array();
        foreach ($list as $value => $display)
        {
            $html[] = $this->getSelectOption($display, $value, $selected);
        }
        return implode('', $html);
    }

    /**
     * Get the select option for the given value.
     *
     * @param  string  $display
     * @param  string  $value
     * @param  string  $selected
     * @return string
     */
    public function getSelectOption($display, $value, $selected)
    {
        if (is_array($display))
        {
            return $this->selectOptionGroup($display, $value, $selected);
        }

        return $this->selectOption($display, $value, $selected);
    }

    /**
     * Create an option group form element.
     *
     * @param  array   $list
     * @param  string  $label
     * @param  string  $selected
     * @return string
     */
    protected function selectOptionGroup($list, $label, $selected)
    {
        $html = array();

        foreach ($list as $value => $display)
        {
            $html[] = $this->selectOption($display, $value, $selected);
        }

        return '<optgroup label="'.$this->e($label).'">'.implode('', $html).'</optgroup>';
    }

    /**
     * Create a select element option.
     *
     * @param  string $display
     * @param  string $value
     * @param  string $selected
     * @return string
     */
    protected function selectOption($display, $value, $selected)
    {
        $selected = $this->getSelectedValue($value, $selected);
        $options = array('value' => $this->e($value), 'selected' => $selected);
        $tagValue = $this->e($display);
        $atts = $this->makeAttributeString($options);
        return "<option{$atts}>{$tagValue}</option>";
    }

    /**
     * Determine if the value is selected.
     *
     * @param  string $value
     * @param  string $selected
     * @param string $attributeValue
     * @return string
     */
    protected function getSelectedValue($value, $selected, $attributeValue = 'selected')
    {
        if (is_array($selected))
        {
            return in_array($value, $selected) ? $attributeValue : null;
        }

        return ((string) $value == (string) $selected) ? $attributeValue : null;
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param  array  $attributes
     * @return string
     */
    public function makeAttributeString($attributes)
    {
        $html = array();

        // For numeric keys we will assume that the key and the value are the same
        // as this will convert HTML attributes such as "required" to a correct
        // form like required="required" instead of using incorrect numerics.
        foreach ((array) $attributes as $key => $value)
        {
            if (is_array($value)) $value = join(' ', $value);
            $element = $this->attributeElement($key, $value);

            if ( ! is_null($element)) $html[] = $element;
        }

        return count($html) > 0 ? ' '.implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param  string  $key
     * @param  string  $value
     * @return string
     */
    protected function attributeElement($key, $value)
    {
        if (is_numeric($key)) $key = $value;

        if ( ! is_null($value)) return $key.'="'.$this->e($value).'"';
    }

    /**
     * @param $value
     * @return string
     */
    protected function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
} 