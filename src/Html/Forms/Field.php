<?php namespace Iyoworks\Html\Forms;

class Field extends ContainedElement {
    public $elementType = 'field';
    protected static $defaultProperties = ['tag' => 'input',
        'type' => 'input', 'value' => null];
    protected $attributes = ['class' => ['form-field']];
    protected $properties = [
        'name' => null,
        'slug' => null,
        'rowable' => true,
        'rules' => '',
        'options' => [],
        'variables' => [],
        'description' => null,
        'baseNames' => [],
        'label' => null,
        'ignoreLabel' => false,
        'ignoreDescription' => false,
        'container' => null
    ];

    public function __construct(array $properties = array(), array $attributes = array(),
                                ElementRendererInterface $renderer = null)
    {
        $this->properties['container'] = Element::make()->addClass('form-group');
        $this->renderCallback = array_pull($properties, 'onRender', $this->renderCallback);
        parent::__construct($properties, $attributes, $renderer);
    }


    /**
     * @param mixed $value
     * @param bool $onTop
     * @return $this
     */
    public function addName($value, $onTop = false)
    {
        $names = $this->getProperty('baseNames');
        if ($names !== false)
        {
            if ($onTop)
                array_unshift($names, $value);
            else
                array_push($names, $value);
            $this->setProperty('baseNames', $names);
        }
        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    public function onGetName($value)
    {
        if ($value === false)
            return null;
        if (is_null($value))
            $value = $this->getProperty('slug');
        $baseNames = $this->getProperty('baseNames');
        if ($baseNames !== false)
            $value = $this->makeFieldName($value, $this->getProperty('baseNames'), $this->muliple);
        return $value;
    }

    /**
     * @return mixed
     */
    public function safeName()
    {
        return str_replace(array('.', '[]', '[', ']'), array('_', '', '.', ''), $this->name);
    }

    /**
     * @param $name
     * @param $baseNames
     * @param $multiple
     * @return string
     */
    protected function makeFieldName($name, $baseNames, $multiple)
    {
        $baseNames = (array) $baseNames;
        $baseNames[] = $name;
        $name = null;
        foreach ($baseNames as $_name) {
            if (!$name)
            {
                if ($_name === false)
                    $name = -1;
                else
                    $name = $_name;
            }
            elseif($name == -1)
            {
                $name = $_name;
                break;
            }
            else
                $name .= '['.$_name.']';
        }

        if ($multiple) return $name.'[]';
        return $name;
    }
}