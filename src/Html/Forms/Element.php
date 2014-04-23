<?php namespace Iyoworks\Html\Forms;

use Illuminate\Support\Fluent;
use Iyoworks\Support\Str;

/**
 * Class Element
 * @package Iyoworks\Html\Forms
 */
class Element extends Fluent {

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
    protected static $defaultProperties = ['tag' => 'div', 'value' => null, 'field' => false];
    /**
     * @var array
     */
    protected $properties = [];
    /**
     * @var array
     */
    protected $elementProperties = [];
    /**
     * @var array|Element[][]
     */
    protected $appendages = [];
    /**
     * @var ElementRendererInterface
     */
    protected $renderer;

    /**
     * @param array $properties
     * @param array $attributes
     * @param ElementRendererInterface $renderer
     */
    public function __construct(array $properties = array(), array $attributes = array(),
                                ElementRendererInterface $renderer = null)
    {
        $this->setProperties(array_merge($this->getDefaultProperties(), $properties));
        $this->fill($attributes);
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
        $element = $this->newInstance($properties, $attributes);
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
        $element = $this->newInstance($properties, $attributes);
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
        if (!$properties instanceof Element) {
            $element = static::make($properties, $attributes, $this->renderer);
        } else {
            $element = $properties;
        }
        return $element;
    }

    /**
     * Add an element as an appendage
     * @param $string
     * @param $element
     */
    protected function addAppendage($string, $element)
    {
        $this->appendages[$string] = $element;
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
            else
            {
                $this->removeProperty($name);
                return $this;
            }
        }
        if (method_exists($this, $method = 'onSet'.Str::studly($name)))
            $value = $this->{$method}($value);
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
        if (method_exists($this, $method = 'onGet'.Str::studly($name)))
            $value = $this->{$method}($value);
        return $value;
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
