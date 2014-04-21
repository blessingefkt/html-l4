<?php namespace Iyoworks\Html\Forms;

class Field extends ContainedElement {
    protected $renderCallback;
    protected $properties = [
        'name' => null,
        'slug' => null,
        'rowable' => true,
        'type' => 'text',
        'rules' => '',
        'options' => [],
        'variables' => [],
        'description' => null,
        'multiple' => false,
        'baseNames' => [],
        'label' => null,
        'ignoreLabel' => false,
        'ignoreDescription' => false
    ];

    protected $elementProperties = ['label', 'container'];

    public function __construct($renderer, array $properties = array(), array $attributes = array())
    {
        $label = array_pull($properties, 'label', array_get($properties, 'slug'));
        $this->setProperty('label', new Element($renderer, ['tag' => 'label', 'value' => $label]));
        parent::__construct($renderer, $properties, $attributes);
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
     * @return string
     */
    public function render()
    {
        if ($this->renderCallback)
            call_user_func($this->renderCallback, $this);

        if (!$this->name)
        {
            $this->name = $this->makeFieldName();
            $this->attributes['name'] = $this->name;
        }
        return parent::render($this);
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

    protected function wrapImplode($array, $before, $after, $prepend = null)
    {
        $str = $prepend;
        foreach (array_values($array) as $item) {
            $str .= $before . $item . $after;
        }
        return $str;
    }

    protected function onGetLabel($label)
    {
        $label->for = $this->name;
        return $label;
    }


    /**
     * @return string
     */
    public function makeFieldName()
    {
        $names = (array) $this->getProperty('baseNames', []);
        $names[] = $this->getProperty('name')  ?: $this->getProperty('slug');
        $name = null;
        foreach ($names as $_name) {
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

        if ($this->multiple) return $name.'[]';
        return $name;
    }

    /**
     * @param $string
     * @return $this
     */
    public function removeProperty($name)
    {
        unset($this->properties[$name]);
        return $this;
    }
} 