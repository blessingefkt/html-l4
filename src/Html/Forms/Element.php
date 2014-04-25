<?php namespace Iyoworks\Html\Forms;

use Illuminate\Support\Contracts\ArrayableInterface;
use Iyoworks\Support\Str;

/**
 * Class Element
 * @package Iyoworks\Html\Forms
 */
class Element implements ArrayableInterface {

    /**
     * @var ElementRendererInterface
     */
    protected static $fieldRenderer;
    /**
     * @var callable
     */
    protected $renderCallback;
    /**
     * @var string
     */
    public $elementType = 'element';
    /**
     * @var array
     */
    protected static $defaultProperties = ['tag' => 'div', 'value' => null];
    /**
     * @var array
     */
    protected $properties = ['tag'];
    /**
     * @var array|Element[][]
     */
    protected $appendages = [];
    /**
     * @var ElementRendererInterface
     */
    protected $renderer;
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param array $properties
     * @param array $attributes
     * @param ElementRendererInterface $renderer
     */
    public function __construct(array $properties = array(), array $attributes = array(),
                                ElementRendererInterface $renderer = null)
    {
        $this->setProperties(array_merge($this->getDefaultProperties(), $properties));
        $this->mergeAttrs($attributes);
        $this->renderer = $renderer ?: static::getFieldRenderer();
    }

    /**
     * @param \Iyoworks\Html\Forms\ElementRendererInterface $fieldRenderer
     */
    public static function setFieldRenderer(ElementRendererInterface $fieldRenderer)
    {
        self::$fieldRenderer = $fieldRenderer;
    }

    /**
     * @throws \UnexpectedValueException
     * @return \Iyoworks\Html\Forms\ElementRendererInterface
     */
    public static function getFieldRenderer()
    {
        if (!self::$fieldRenderer)
            throw new \UnexpectedValueException('An an instance of \Iyoworks\Html\RendererInterface was expected');
        return self::$fieldRenderer;
    }

    /**
     * @param array $properties
     * @param array $attributes
     * @param \Iyoworks\Html\Forms\ElementRendererInterface $renderer
     * @return Element|static
     */
    public static function make(array $properties = [], array $attributes = [], $renderer = null)
    {
        return new static($properties,  $attributes, $renderer);
    }

    /**
     * @param Element|array $properties
     * @param array $attributes
     * @return Element
     */
    public function prepend($properties = [], $attributes = [])
    {
        if (!$properties instanceof Element) {
            $element = $this->newInstance($properties, $attributes);
        }
        else
            $element = $properties;
        $this->addAppendage('prepend', $element);
        return $element;
    }

    /**
     * @param Element|array $properties
     * @param array $attributes
     * @return Element
     */
    public function append($properties = [], $attributes = [])
    {
        if (!$properties instanceof Element) {
            $element = $this->newInstance($properties, $attributes);
        }
        else
            $element = $properties;
        $this->addAppendage('append', $element);
        return $element;
    }

    /**
     * @param \Iyoworks\Html\Forms\Element|array $properties
     * @param $attributes
     * @return Element
     */
    public function newInstance($properties = [], $attributes = [])
    {
        return new Element($properties, $attributes, $this->renderer);
    }

    /**
     * Add an element as an appendage
     * @param $string
     * @param $element
     */
    protected function addAppendage($string, $element)
    {
        if ($index = $element->slug)
            $this->appendages[$string][$index] = $element;
        else
            $this->appendages[$string][] = $element;
    }

    /**
     * @param string $group
     * @return array|Element[]
     */
    public function getAppendages($group = null)
    {
        if($group) return array_get($this->appendages, $group, []);
        return $this->appendages;
    }

    /**
     * Get an attribute from the container.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->isProperty($key))
            $value = $this->getProperty($key, $default);
        else
            $value = $this->getAttr($key, $default);

        if (method_exists($this, $method = 'onGet'.Str::studly($key)))
            return $this->{$method}($value);

        return $value;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getAttr($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes))
            $value = $this->attributes[$key];
        else
            $value = $default;
        return value($value);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (method_exists($this, $method = 'onSet'.Str::studly($key)))
            $this->{$method}($value);
        else
        {
            if ($this->isProperty($key))
                $this->setProperty($key, $value);
            else
                $this->setAttr($key, $value);
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setAttr($key, $value)
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
        if (is_array($class))
        {
            array_map([$this, 'addClass'], $class);
        }
        else
        {
            $classes = (array) array_get($this->attributes, 'class', []);
            if (!is_array($class))
                $class = explode(' ', $class);
            $classes = array_merge($classes, $class);
            $this->attributes['class'] = array_unique($classes);
        }
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function removeClass($class)
    {
        if (is_array($class))
        {
            array_map([$this, 'removeClass'], $class);
        }
        else
        {
            $classes = (array) array_get($this->attributes, 'class', []);
            if ($key = array_search($class, $classes))
            {
                unset($classes[$key]);
            }
            $this->attributes['class'] = $classes;
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
     * @param array $array
     * @return $this
     */
    public function mergeAttrs(array $array)
    {
        $this->attributes = array_merge_recursive($this->attributes, $array);
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

    /**
     * @param $key
     * @return bool
     */
    public function isProperty($key)
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasProperty($key)
    {
        return isset($this->properties[$key]);
    }

    /**
     * @return string
     */
    public function getAttributes()
    {
        return $this->renderer->makeAttributeString($this->attributes);
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
     * @param $value
     * @return $this
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
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
        return value($value);
    }

    /**
     * @param $name
     * @return $this
     */
    public function removeProperty($name)
    {
        unset($this->properties[$name]);
        return $this;
    }

    /**
     * @param $callable
     * @return $this
     */
    public function onRender($callable)
    {
        $this->renderCallback = $callable;
        return $this;
    }

    /**
     * @return string
     */
    public function html()
    {
        if ($this->renderCallback)
            call_user_func($this->renderCallback, $this);
        return $this->renderer->render($this, $this->elementType);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }


    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
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
            $this->setAttr($key, $value);
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
        if (count($parameters) > 0)
        {
            $this->set($method, $parameters[0]);
            return $this;
        }
        return $this->get($method);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->html();
    }

    /**
     * @return array
     */
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
