<?php namespace Iyoworks\Html\Forms;

use Illuminate\Support\Collection;
use Iyoworks\Support\Traits\ExtendableTrait;

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
    use ExtendableTrait;
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
        'checkbox' => ['type' => 'checkbox', 'checkable' => 'input', 'format' => '<label for="[name]">[checkable][label]</label>'],
        'radio' => ['type' => 'radio', 'checkable' => 'input'],
        'password' => ['type' => 'password'],
        'label' => ['tag' => 'label'],
        'fieldset' => ['tag' => 'fieldset'],
        'legend' => ['tag' => 'legend'],
        'select' => ['tag' => 'select', 'checkable' => 'select'],
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
     * @return bool
     */
    public static function addMacro($name, $callable)
    {
        static::extend($name, $callable);
    }

    /**
     * @param $name
     * @return bool
     */
    public static function isMacro($name)
    {
        return isset(static::$extensions[$name]);
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
        return $this->addField($type, $slug, $value, $properties);
    }

    /**
     * @param $value
     * @param array $properties
     * @return Field
     */
    public function submit($value = null, array $properties = null)
    {
        $properties = array_merge(['rowable' => false, 'label' => false, 'order' => 1000,
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
     * @param $type
     * @param $slug
     * @param null $value
     * @param array $properties
     * @throws \ErrorException
     * @return Field
     */
    public function addField($type, $slug, $value = null, array $properties = null)
    {
        if (empty($type))
            throw new \ErrorException("{$slug} field does not have a type");

        if (static::isMacro($type))
            return static::macro($type, $slug, $value, $properties);

        if (isset($this->defaultElementTypes[$type]))
            $defaults = $this->defaultElementTypes[$type];
        else
            $defaults = [];

        $defaults['type'] = $type;
        $attributes = array_pull($properties, 'attr', []);
        $properties = array_merge($defaults, $properties ?: []);
        $properties['slug'] = $slug;
        $properties['value'] = $value;

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
        return $this->rowClass.round($this->maxColumns / $rowSize);
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
     * @return int
     */
    public function runCallbacks($group)
    {
        $cnt = 0;
        if (!isset($this->callbacks[$group])) return $cnt;
        foreach ($this->callbacks[$group] as $callback)
        {
            foreach ($this->elements as $elem)
            {
                if ($elem->elementType == $group)
                {
                    $cnt++;
                    call_user_func($callback, $elem);
                }
            }
        }
        return $cnt;
    }

    /**
     * @return Collection|Field[]
     */
    public function getElementsByRow()
    {
        $countBase = count($this->elements).microtime(true);
        $grouped = $this->getElements()
            ->filter(function($field) {
                return $field->getProperty('rowable');
            })
            ->groupBy(function($field) use ($countBase) {
                static $i = 0;
                return $field->getProperty('row') ?: ($field->row = $countBase + $i++);
            });
        if($grouped->has(0))
        {
            $unnamed = $grouped->get(0);
            unset($grouped[0]);
            foreach ($unnamed as $field) {
                $grouped->push([$field]);
            }
        }
        return $grouped;
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
        foreach ($this->getElementsByRow() as $row)
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
     * @param $type
     * @param $slug
     * @param mixed $value
     * @param array $properties
     * @return Element
     */
    public function macro($type, $slug, $value = null, array $properties = null)
    {
        $callable = static::$extensions[$type];
        return call_user_func($callable, $slug, $value, $properties ?: [], $this);
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
        if($this->isMacro($method) || isset($this->defaultElementTypes[$method]))
        {
            array_unshift($parameters, $method);
            return call_user_func_array([$this, 'addField'], $parameters);
        }
        return parent::__call($method, $parameters);
    }
}