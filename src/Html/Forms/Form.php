<?php namespace Iyoworks\Html\Forms;

use Illuminate\Support\Collection;

class Form extends Element {
    /**
     * @var RendererInterface
     */
    protected static $fieldRenderer;
    /**
     * @var string
     */
    protected $fieldClass = 'Iyoworks\Html\Forms\Field';
    /**
     * @var array|Field[]
     */
    protected $fields = [];
    /**
     * @var array|callable
     */
    protected $callbacks = ['form' => [], 'fields' => []];

    protected $properties = [
        'title' => null,
        'tag' => 'form',
        'csrfToken' => true,
        'perRow' => 1,
        'files' => true,
        'baseFieldName' => null,
        'rowAttributes' => ['class' => 'field-row row'],
        'submit' => null
    ];
    protected $elementProperties = ['submit'];

    function __construct(RendererInterface $renderer, array $properties = array(), array $attributes = array())
    {
        parent::__construct($renderer, $properties, $attributes);
        $this->setProperty('submit', new ContainedElement($renderer, ['tag' => 'button', 'value' => 'Save'],
            ['type' => 'submit', 'class' => 'btn btn-success btn-sm', 'field' => true]));
    }

    /**
     * @param \Iyoworks\Html\Forms\RendererInterface $fieldRenderer
     */
    public static function setFieldRenderer(RendererInterface $fieldRenderer)
    {
        self::$fieldRenderer = $fieldRenderer;
    }

    /**
     * @throws \UnexpectedValueException
     * @return \Iyoworks\Html\Forms\RendererInterface
     */
    public static function getFieldRenderer()
    {
        if (!self::$fieldRenderer)
            throw new \UnexpectedValueException('An an instance of \Iyoworks\Html\RendererInterface was expected');
        return self::$fieldRenderer;
    }

    /**
     * @param $slug
     * @param $value
     * @param array $properties
     * @return Field
     */
    public function addHidden($slug, $value, array $properties = [])
    {
        $properties = array_merge(['value' => $value,
                'type' => 'hidden',
                'tag' => 'input',
                'rowable' => false,
                'field' => true,
                'container' => false,
                'label' => false,
                'slug' => $slug],
            $properties);
        return $this->addField($slug, $properties);
    }

    /**
     * @param $slug
     * @param array $properties
     * @param array $attributes
     * @return Field
     */
    public function add($slug, $properties = [],  $attributes = [])
    {
        return $this->addField($slug, $properties, $attributes);
    }

    /**
     * @param $slug
     * @param array $properties
     * @param array $attributes
     * @return Field
     */
    public function addField($slug, $properties = [],  $attributes = [])
    {
        $properties['slug'] = $slug;
        $field = $this->makeField($properties, $attributes);
        $field->setProperty('field', true);
        return $this->addElement($field, $slug);
    }

    /**
     * @param Element $element
     * @param null $index
     * @return Element
     */
    public function addElement(Element $element, $index = null)
    {
        if ($index)
            $this->fields[$index] = $element;
        else
            $this->fields[] = $element;
        if(!$element->hasProperty('order'))
            $element->setProperty('order', count($this->fields));
        return $element;
    }

    /**
     * @param array $properties
     * @return Element
     */
    protected function makeElement(array $properties)
    {
        $element = new Element($this->renderer, $properties);
        return $element;
    }

    /**
     * @param $slug
     * @param mixed $value
     * @return bool|Field
     */
    public function getField($slug, $value = null)
    {
        if(isset($this->fields[$slug]))
        {
            $field = $this->fields[$slug];
            if($value) $field->value = $value;
            return $field;
        }
        return false;
    }

    /**
     * @param array $attributes
     * @return Element
     */
    public function rowElement(array $attributes = null)
    {
        if(!$attributes) $attributes = $this->rowAttributes;
        return new Element($this->renderer, [], $attributes);
    }

    public function runCallbacks($group)
    {
       if($group == 'fields')
       {
           foreach ($this->callbacks[$group] as $callback)
           {
               foreach ($this->fields as $field)
               {
                   call_user_func($callback, $field);
               }
           }
       }
        else
        {
            foreach ($this->callbacks[$group] as $callback)
            {
                call_user_func($callback, $this);
            }
        }
    }

    /**
     * @return array|Field[]
     */
    public function getFieldArray()
    {
        return $this->fields;
    }

    /**
     * @return \Illuminate\Support\Collection|Field[]
     */
    public function getFields()
    {
        $collection = new Collection($this->fields);
        $collection->sortBy('order');
        return $collection;
    }

    /**
     * @param array $properties
     * @param array $attributes
     * @return Field
     */
    protected function makeField($properties, $attributes)
    {
        if ($atts = array_pull($properties, 'attr'))
            $attributes = array_merge($attributes, $atts);
        $field = new $this->fieldClass(static::$fieldRenderer, $properties, $attributes);
        return $field;
    }

    /**
     * @param $property
     * @param mixed $default
     * @return array
     */
    public function fetchProperty($property, $default = null)
    {
        $results = [];
        foreach ($this->fields as $key => $field) {
            $results[$key] = $field->getProperty($property,$default);
        }
        return $results;
    }

    public function onRenderField($callable)
    {
        $this->callbacks['fields'][] = $callable;
    }

    public function onRender($callable)
    {
        $this->callbacks['form'][] = $callable;
    }

    /**
     * @param string $fieldClass
     */
    public function setFieldClass($fieldClass)
    {
        $this->fieldClass = $fieldClass;
    }

    /**
     * @return string
     */
    public function getFieldClass()
    {
        return $this->fieldClass;
    }

    /**
     * @return Collection
     */
    public function getRowableFields()
    {
        return $this->getFields()->filter(function($field){
            return $field->rowable;
        })->chunk($this->perRow, true);
    }

    /**
     * @return Collection
     */
    public function getNonRowableFields()
    {
        return $this->getFields()->filter(function($field){
            return !$field->rowable;
        });
    }

    /**
     * @return string
     */
    public function renderFields()
    {
        $this->runCallbacks('fields');
        return $this->renderer->renderFormFields($this);
    }

    /**
     * @return string
     */
    public function open()
    {
        $this->runCallbacks('form');
        return $this->renderer->renderFormOpen($this);
    }

    /**
     * @return string
     */
    public function close()
    {
        return $this->renderer->renderFormClose($this);
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->open().$this->renderFields().$this->close();
    }

    public function __toString()
    {
        return $this->render();
    }
}