<?php namespace Iyoworks\Html\Forms;

interface RendererInterface  {

    public function renderElement(Element $element);

    public function renderFormFields(Form $form);

    public function renderFormOpen(Form $form);

    public function renderFormClose(Form $form);
} 