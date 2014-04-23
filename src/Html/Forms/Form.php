<?php namespace Iyoworks\Html\Forms;

use Illuminate\Support\Collection;

/**
 * Class Form
 * @package Iyoworks\Html\Forms
 * @method Field text() 	text(string $slug, string $value = null, array $properties = null)
 * @method Field textarea() 	textarea(string $slug, string $value = null, array $properties = null)
 * @method Field label() 	label(string $slug, string $value = null, array $properties = null)
 * @method Field fieldset() 	fieldset(string $slug, string $value = null, array $properties = null)
 * @method Field legend() 	legend(string $slug, string $value = null, array $properties = null)
 * @method Field select() 	select(string $slug, string $value = null, array $properties = null)
 * @method Field optgroup() 	optgroup(string $slug, string $value = null, array $properties = null)
 * @method Field option() 	option(string $slug, string $value = null, array $properties = null)
 * @method Field button() 	button(string $slug, string $value = null, array $properties = null)
 * @method Field datalist() 	datalist(string $slug, string $value = null, array $properties = null)
 * @method Field keygen() 	keygen(string $slug, string $value = null, array $properties = null)
 * @method Field output() 	output(string $slug, string $value = null, array $properties = null)
 */
class Form extends Element {
    protected static $macros = [];
    /**
     * @var FormRendererInterface
     */
    protected $renderer;
    /**
     * @var string
     */
    public $elementType = 'form';
    /**
     * @var array|Field[]
     */
    protected $elements = [];
    /**
     * @var array|callable
     */
    protected $callbacks = ['form' => [], 'fields' => [], 'elements' => []];

    protected $defaultElementTypes = [
        'text' => ['type' => 'text'],
        'textarea' => ['tag' => 'textarea'],
        'label' => ['tag' => 'label'],
        'fieldset' => ['tag' => 'fieldset'],
        'legend' => ['tag' => 'legend'],
        'select' => ['tag' => 'select'],
        'optgroup' => ['tag' => 'optgroup'],
        'option' => ['tag' => 'option'],
        'button' => ['tag' => 'button', 'class' => 'btn'],
        'datalist' => ['tag' => 'datalist'],
        'keygen' => ['tag' => 'keygen'],
        'output' => ['tag' => 'output'],
    ];

    protected $properties = [
        'tag' => 'form',
        'csrfToken' => true,
        'perRow' => 1,
        'files' => true,
        'maxColumns' => 12,
        'rowClass' => 'col-md-',
        'baseFieldName' => null,
        'rowAttributes' => ['class' => 'field-row row'],
        'fieldAttributes' => []
    ];

    /**
     * @param array $properties
     * @param array $attributes
     * @param FormRendererInterface $renderer
     */
    public function __construct(array $properties = array(), array $attributes = array(),
                                FormRendererInterface $renderer = null)
    {
        parent::__construct($properties, $attributes, $renderer);
    }

    /**
     * @param $name
     * @param $callable
     */
    public static function addMacro($name, $callable)
    {
        static::$macros[$name] = $callable;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function isMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * @param $type
     * @param $slug
     * @param $value
     * @param array $properties
     * @return Field
     */
    public function input($type, $slug, $value = null, array $properties = null)
    {
        $properties['value'] = $value;
        $properties['type'] = $type;
        return $this->add($slug, $properties);
    }

    /**
     * @param $value
     * @param array $properties
     * @return Field
     */
    public function submit($value = null, array $properties = null)
    {
         $properties = array_merge(['rowable' => false, 'label' => false,
             'attr' => ['class' => 'btn btn-primary']], $properties ?: []);
        $button = $this->button('submit', $value, $properties);
        $button->setAttr('type', 'submit');
        $button->name = false;
        return $button;
    }

    /**
     * @param $slug
     * @param $value
     * @param array $properties
     * @return Field
     */
    public function hidden($slug, $value = null, array $properties = null)
    {
        $properties = array_merge(['rowable' => false, 'label' => false, 'container' => false],
            $properties ?: []);
        return $this->input('hidden', $slug, $value, $properties);
    }

    /**
     * @param $slug
     * @param array $properties
     * @param array $attributes
     * @return Field
     */
    public function add($slug, $properties = null,  $attributes = null)
    {
        return $this->addField($slug, $properties, $attributes);
    }

    /**
     * @param $slug
     * @param array $properties
     * @param array $attributes
     * @return Field
     */
    public function addField($slug, $properties = null,  $attributes = null)
    {
        $properties['slug'] = $slug;
        if ($type = array_get($properties, 'type'))
        {
            $defaults = array_get($this->defaultElementTypes, $type, []);
            $defaults['type'] = $type;
            $properties = array_merge($defaults, $properties ?: []);
        }
        $field = $this->makeField($properties, $attributes);
        return $this->addElement($field, $slug);
    }

    /**
     * @param array $properties
     * @param array $attributes
     * @return Field
     */
    protected function makeField(array $properties = null, array $attributes = null)
    {
        $field = Field::make($properties ?: [], $this->fieldAttributes, $this->renderer);
        if($attributes) $field->mergeAttrs($attributes);
        return $field;
    }

    /**
     * @param Element $element
     * @param null $index
     * @return Element
     */
    public function addElement(Element $element, $index = null)
    {
        if ($atts = array_get($element->getProperties(), 'attr'))
        {
            $attributes = array_merge($element->toArray(), $atts);
            $element->mergeAttrs($attributes);
            $element->removeProperty('attr');
        }

        if(!$element->hasProperty('order'))
            $element->setProperty('order', count($this->elements));

        if ($index)
            $this->elements[$index] = $element;
        else
            $this->elements[] = $element;
        return $element;
    }

    /**
     * @param array $properties
     * @return Element
     */
    protected function makeElement(array $properties = [])
    {
        $element = Element::make($properties, [], $this->renderer);
        return $element;
    }

    /**
     * return the field if it exists
     * @param $slug
     * @return bool|Field
     */
    public function getField($slug)
    {
        if(isset($this->elements[$slug]))
            return $this->elements[$slug];
        return false;
    }

    /**
     * If the field exists, set its value
     * @param $slug
     * @param $value
     * @return $this
     */
    public function setValue($slug, $value)
    {
        if(isset($this->elements[$slug]))
        {
            $field = $this->elements[$slug];
            $field->value = $value;
        }
        return $this;
    }

    public function rowClass($rowSize)
    {
        return $this->rowClass.round($this->maxColumns / min($this->perRow, $rowSize));
    }

    /**
     * @param array $attributes
     * @return Element
     */
    public function rowElement(array $attributes = null)
    {
        if(!$attributes) $attributes = $this->rowAttributes;
        return $this->makeElement()->fill($attributes);
    }

    /**
     * Run callbacks for fields or elements
     * @param $group
     */
    public function runCallbacks($group)
    {
        if (isset($this->callbacks[$group]))
            foreach ($this->callbacks[$group] as $k => $callback)
                foreach ($this->elements as $field)
                {
                    if ($field->elementType == $group)
                        call_user_func($callback, $field);
                }
        {
            }
    }

    /**
     * @return Collection|Field[]
     */
    public function getRowableElements()
    {
        return $this->getElements()->filter(function($field) {
            return (bool) $field->rowable;
        })->chunk($this->perRow, true);
    }

    /**
     * @return Collection|Field[]
     */
    public function getNonRowableElements()
    {
        return $this->getElements()->filter(function($field) {
            return !$field->rowable;
        });
    }

    /**
     * @return string
     */
    public function onGetValue()
    {
        $this->runCallbacks('field');
        $this->runCallbacks('element');
        $formStr = [];
        foreach ($this->getRowableElements() as $row)
        {
            $rowElement = $this->rowElement();
            foreach ($row as $field)
            {
                if($field->container)
                {
                    $_size = $this->rowClass(count($row));
                    $field->container->addClass($_size);
                }
                $rowElement->value .= $field->html();
            }
            $formStr[] = $rowElement->html();
        }
        foreach ($this->getNonRowableElements() as $field)
        {
            $formStr[] = $field->html();
        }

        return join(PHP_EOL, $formStr);
    }

    /**
     * @return string
     */
    public function open()
    {
        $this->runCallbacks('form');
        return $this->renderer->renderOpen($this);
    }

    /**
     * @return string
     */
    public function close()
    {
        return $this->renderer->renderClose($this);
    }

    /**
     * @return \Illuminate\Support\Collection|Field[]
     */
    public function getElements()
    {
        $collection = new Collection($this->elements);
        $collection->sortBy('order');
        return $collection;
    }

    /**
     * @return array|Element[]
     */
    public function getElementArray()
    {
        return $this->elements;
    }

    /**
     * @param $property
     * @param mixed $default
     * @return array
     */
    public function fetchProperty($property, $default = null)
    {
        $results = [];
        foreach ($this->elements as $key => $field) {
            $results[$key] = $field->getProperty($property,$default);
        }
        return $results;
    }

    /**
     * @param $callable
     * @return $this
     */
    public function onRenderForm($callable)
    {
        $this->callbacks['form'][] = $callable;
        return $this;
    }

    /**
     * @param $callable
     * @return $this
     */
    public function onRenderField($callable)
    {
        $this->callbacks['field'][] = $callable;
        return $this;
    }

    /**
     * @param $callable
     * @return $this
     */
    public function onRenderElement($callable)
    {
        $this->callbacks['element'][] = $callable;
        return $this;
    }

    /**
     * @param $name
     * @param $parameters
     * @return Field|mixed
     */
    public function useMacro($name, $parameters)
    {
        $callable = static::$macros[$name];
        $parameters[] = $this;
        call_user_func_array($callable, $parameters);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->html();
    }

    /**
     * Handle dynamic calls to the container to set attributes
     * or create fields dynamically
     * @param  string  $method
     * @param  array   $parameters
     * @return Field|mixed
     */
    public function __call($method, $parameters)
    {
        if(static::isMacro($method))
            return $this->useMacro($method, $parameters);
        if(isset($this->defaultElementTypes[$method]))
        {
            array_unshift($parameters, $method);
            return call_user_func_array([$this, 'input'], $parameters);
        }
        return parent::__call($method, $parameters);
    }
}