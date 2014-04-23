<?php namespace Iyoworks\Html\Forms;

use Illuminate\View\Environment;
use Iyoworks\Html\FormBuilder;
use Iyoworks\Html\HtmlBuilder;
use Iyoworks\Support\Str;

abstract class BaseElementRenderer implements ElementRendererInterface  {
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