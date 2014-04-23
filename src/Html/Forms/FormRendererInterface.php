<?php namespace Iyoworks\Html\Forms;

interface FormRendererInterface extends ElementRendererInterface  {

    /**
     * @param Form $element
     * @return string
     */
    public function renderOpen(Form $element);

    /**
     * @param Form $element
     * @return string
     */
    public function renderClose(Form $element);
} 