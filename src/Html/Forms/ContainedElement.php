<?php namespace Iyoworks\Html\Forms;


class ContainedElement extends Element {

    protected static $defaultProperties = ['tag' => 'div', 'value' => null, 'container' => null];

    protected $elementProperties = ['container'];

    public function __construct($renderer, array $properties = array(), array $attributes = array())
    {
        $this->setProperty('container', new Element($renderer, [], ['class' => 'form-group']));
        parent::__construct($renderer, $properties, $attributes);
    }
} 