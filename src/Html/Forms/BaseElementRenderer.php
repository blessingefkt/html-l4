<?php namespace Iyoworks\Html\Forms;

use Illuminate\View\Environment;
use Iyoworks\Html\FormBuilder;
use Iyoworks\Html\HtmlBuilder;
use Iyoworks\Support\Str;

abstract class BaseElementRenderer implements ElementRendererInterface  {

    public function getSelectFieldOptions($list, $selected = null)
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
            return $this->optionGroup($display, $value, $selected);
        }

        return $this->option($display, $value, $selected);
    }

    /**
     * Create an option group form element.
     *
     * @param  array   $list
     * @param  string  $label
     * @param  string  $selected
     * @return string
     */
    protected function optionGroup($list, $label, $selected)
    {
        $html = array();

        foreach ($list as $value => $display)
        {
            $html[] = $this->option($display, $value, $selected);
        }

        return '<optgroup label="'.$this->e($label).'">'.implode('', $html).'</optgroup>';
    }

    /**
     * Create a select element option.
     *
     * @param  string  $display
     * @param  string  $value
     * @param  string  $selected
     * @return string
     */
    protected function option($display, $value, $selected)
    {
        $selected = $this->getSelectedValue($value, $selected);

        $options = array('value' => $this->e($value), 'selected' => $selected);

        return '<option'.$this->makeAttributeString($options).'>'.$this->e($display).'</option>';
    }

    /**
     * Determine if the value is selected.
     *
     * @param  string  $value
     * @param  string  $selected
     * @return string
     */
    protected function getSelectedValue($value, $selected)
    {
        if (is_array($selected))
        {
            return in_array($value, $selected) ? 'selected' : null;
        }

        return ((string) $value == (string) $selected) ? 'selected' : null;
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