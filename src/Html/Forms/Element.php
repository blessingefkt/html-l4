<?php namespace Iyoworks\Html\Forms;

use Illuminate\Support\Fluent;
use Iyoworks\Support\Str;

class Element extends Fluent {
    protected static $defaultProperties = ['tag' => 'div', 'value' => null, 'field' => false];
    protected $properties = [];
    protected $elementProperties = [];
    /**
     * @var RendererInterface
     */
    protected $renderer;

    function __construct(RendererInterface $renderer, array $properties = array(), array $attributes = array())
    {
        $this->renderer = $renderer;
        $this->setProperties(array_merge($this->getDefaultProperties(), $properties));
        $this->fill($attributes);
    }

    /**
     * @param array $properties
     * @param array $attributes
     * @param \Iyoworks\Html\Forms\RendererInterface $renderer
     * @return Element|static
     */
    public static function make(array $properties = [], array $attributes = [], $renderer = null)
    {
        return new static($renderer ?: static::getFieldRenderer(), $properties,  $attributes);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function addClass($class)
    {
        $this->attributes['class'] = trim($this->get('class').' '.$class);
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function removeClass($class)
    {
        $classes = array_get($this->attributes, 'class', null);
        if ($classes)
        {
            $this->attributes['class'] = str_replace(trim($class), '', $classes);
        }
        return $this;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function fill(array $array)
    {
        $this->attributes = $array;
        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->attributes[$key]);
    }

    public function hasProperty($key)
    {
        return isset($this->properties[$key]);
    }

    /**
     * @return string
     */
    public function getAttributes()
    {
        return \HTML::attributes($this->toArray());
    }

    public function setProperty($name, $value)
    {
        if (in_array($name, $this->elementProperties))
        {
            if ($value !== false)
            {
                if (! ($value instanceof Element))
                {
                    $element = $this->getProperty($name, new Element($this->renderer));
                    $value = $element->value($value);
                }
            }
        }
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $value)
        {
            $this->setProperty($name, $value);
        }
        return $this;
    }

    /**
     * @param $name
     * @param mixed $default
     * @return mixed
     */
    public function getProperty($name, $default = null)
    {
        if (!$this->hasProperty($name)) return $default;
        $value = $this->properties[$name];
        if (method_exists($this, $method = 'onGet'.Str::studly($name)))
            $value = $this->{$method}($value);
        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isProperty($key)
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->isProperty($key))
            return $this->getProperty($key);
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        if ($this->isProperty($key))
            $this->setProperty($key, $value);
        else
            $this->set($key, $value);
    }

    /**
     * Handle dynamic calls to the container to set attributes.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return \Illuminate\Support\Fluent
     */
    public function __call($method, $parameters)
    {
        if ($this->isProperty($method))
        {
            $isSetterCall = count($parameters) > 0 && $parameters[0] !== '';
            if ($isSetterCall)
                $this->setProperty($method, $parameters[0]);
            else
                return $this->getProperty($method);
        }
        else
            $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;
        return $this;
    }

    public function render()
    {
        if($this->field)
        {
            $this->attributes['value'] = $this->getProperty('value');
            $this->attributes['type'] = $this->getProperty('type');
            if ($this->multiple)
            {
                $this->attributes['multiple'] = 'multiple';
                unset($this->attributes['value']);
            }
        }
        return $this->renderer->renderElement($this);
    }

    public function __toString()
    {
        return $this->render();
    }

    protected function getDefaultProperties()
    {
        return array_merge(static::$defaultProperties, $this->properties);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

}
