<?php namespace Iyoworks\Html\Forms;

class Field extends ContainedElement {
    protected $elementType = 'field';
    protected $properties = [
        'tag' => 'input',
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
     * @param Element $label
     * @return Element
     */
    protected function onGetLabel($label)
    {
        $label->for = $this->name;
        return $label;
    }

    /**
     * @param Element $label
     * @return Element
     */
    protected function onSetLabel($label)
    {
        $label->tag = 'label';
        return $label;
    }

    /**
     * @param $value
     * @return string
     */
    public function onGetName($value)
    {
        return $this->makeFieldName($value, $this->getProperty('baseNames'), $this->muliple);
    }

    /**
     * @return bool|mixed
     */
    public function dotName()
    {
        if ($name = $this->name)
            return str_replace(['[', ']'], ['.', ''], $name);
        return false;
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