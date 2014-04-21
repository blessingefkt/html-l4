<?php namespace Iyoworks\Html\Forms;

use Illuminate\View\Environment;
use Iyoworks\Support\Str;

class FormRenderer implements RendererInterface  {
    /**
     * @var Environment
     */
    protected $env;
    /**
     * @var string
     */
    protected $defaultView = '_form-field', $formView = null;
    /**
     * @var int
     */
    protected $maxColumns = 12;
    /**
     * @var array
     */
    protected $fieldViews = [];
    protected $formFields = ['checkbox','file','hidden','password','radio','reset','submit','text'];

    public function __construct(Environment $env, $defaultView)
    {
        $this->env = $env;
        $this->defaultView = $defaultView;
    }

    /**
     * @param Form $form
     * @param array $data
     * @return string
     */
    public function renderFormFields(Form $form, array $data = [])
    {
        if ($this->formView)
        {
            return $this->renderFormWithView($this->formView, $form, $data);
        }
        $formStr = '';
        foreach ($form->getRowableFields() as $row)
        {
            $rowElement = $form->rowElement();
            foreach ($row as $field)
            {
                if($field->container)
                {
                    $_size = $this->rowClass($form->perRow, count($row));
                    $field->container->addClass($_size);
                }
                $rowElement->value .= $field->render();
            }
            $formStr .= $rowElement->render();
        }
        foreach ($form->getNonRowableFields() as $field)
        {
            $formStr .= $field->render();
        }

        $form->value .= $formStr;
        return $form->value;
    }

    /**
     * @param Form $form
     * @return string
     */
    public function renderFormOpen(Form $form)
    {
        $form->runCallbacks('form');
        $method = Str::lower($form->method ?: 'post');
        $form->set('method', Str::upper($method));

        if($method !== 'get' || $method !== 'post')
        {
            $form->addHidden('_method', $method, ['baseNames' => false]);
            $form->set('method', 'POST');
        }

        return sprintf("<%s%s>\n", $form->tag(), $form->getAttributes());
    }

    /**
     * @param Form $form
     * @return string
     */
    public function renderFormClose(Form $form)
    {
        $tail = $form->submit;
        if ($form->csrfToken)
        {
            $tail .= "\n".\Form::token();
        }
        #$form->value .=  $form->submit->render();
        return sprintf("\n%s</%s>\n", $tail, $form->tag());
    }

    public function renderElement(Element $element)
    {
        if($element->container)
        {
            if (\Session::has('errors'))
            {
                $errors = \Session::get('errors');
                $_name = $element->slug;
                if ($errors->has($_name))
                {
                    $element->container->addClass('has-error');
                    $element->setProperty('errMsg', $errors->first($_name));
                }
            }
        }
        if ($element instanceof Field)
            return $this->renderField($element);

        if ($element->hasProperty('container'))
        {
            $container = $element->container;
            $container->value = $this->makeElementHTML($element);
            $element = $container;
        }

        return $this->makeElementHTML($element);
    }


    /**
     * @param Field $field
     * @return string
     */
    protected function renderField(Field $field)
    {
        $view = $field->getProperty('view', $this->getTypeView($field->type));
        if (!$view) $view = $this->defaultView;
        $_view = $this->env->make($view, compact('field'))->render();
        if ($field->container)
        {
            $field->container->value = $_view;
            return $this->makeElementHTML($field->container);
        }
        return $_view;
    }

    protected function makeElementHTML(Element $element)
    {
        if (in_array($element->type, $this->formFields))
        {
            return sprintf("<%s%s></%s>\n", $element->tag(), $element->getAttributes(), $element->tag());
        }
        return sprintf("<%s%s>\n%s\n</%s>\n",
            $element->tag(), $element->getAttributes(), $element->value(), $element->tag());
    }


    /**
     * @param $view
     * @param Form $form
     * @param array $data
     * @return string
     */
    protected function renderFormWithView($view, Form $form, array $data)
    {
        $data['form'] = $form;
        $data['fields'] = $form->getFields();
        return $this->env->make($view, $data)->render();
    }

    public function rowClass($maxItems, $rowSize)
    {
        return 'col-md-'.round($this->maxColumns / min($maxItems, $rowSize));
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeView($type)
    {
        return isset($this->fieldViews[$type]) ? $this->fieldViews[$type] : null;
    }

    /**
     * @param $type
     * @param $view
     */
    public function setTypeView($type, $view)
    {
        $this->fieldViews[$type] = $view;
    }

    /**
     * @param int $maxColumns
     */
    public function setMaxColumns($maxColumns)
    {
        $this->maxColumns = $maxColumns;
    }

    /**
     * @return int
     */
    public function getMaxColumns()
    {
        return $this->maxColumns;
    }

    /**
     * @param string $formView
     */
    public function setFormView($formView)
    {
        $this->formView = $formView;
    }

    /**
     * @return string
     */
    public function getFormView()
    {
        return $this->formView;
    }

    /**
     * @param \Illuminate\View\Environment $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * @return \Illuminate\View\Environment
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @param string $defaultView
     */
    public function setDefaultView($defaultView)
    {
        $this->defaultView = $defaultView;
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return $this->defaultView;
    }

    /**
     * @return array
     */
    public function getFieldViews()
    {
        return $this->fieldViews;
    }

} 