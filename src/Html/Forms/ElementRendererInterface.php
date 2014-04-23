<?php namespace Iyoworks\Html\Forms;

interface ElementRendererInterface  {

    /**
     * @param Element $element
     * @param string $elementType
     * @return string
     */
    public function render(Element $element, $elementType);

    /**
     * @param array $attributes
     * @return string
     */
    public function makeAttributeString($attributes);
} 