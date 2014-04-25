<?php namespace Iyoworks\Html\Forms;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\Store as Session;
use Iyoworks\Support\Str;

class LaravelFormRenderer extends BaseElementRenderer implements FormRendererInterface  {
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $dispatcher;
    /**
     * @var array
     */
    protected $formFields = ['checkbox','file','hidden','password',
        'radio','reset','submit','text', 'input'];

    function __construct(Session $session = null, Dispatcher $dispatcher = null)
    {
        $this->session = $session;
        $this->dispatcher = $dispatcher;
    }


    /**
     * @param Element $element
     * @param string $elementType
     * @return string
     */
    public function render(Element $element, $elementType)
    {
        $this->fire('rendering', $elementType, $element);
        switch($elementType)
        {
            case 'field':
                $this->fire('field.rendering', $element->type, $element);
                $output = $this->makeFieldHTML($element);
                break;
            case 'form':
                $output = $this->makeFormHTML($element);
                break;
            default :
                $output = $this->makeElementHTML($element);
        }
        $this->fire('rendered', $elementType, $element);

        foreach ($element->getAppendages('prepend') as $_elem)
        {
            $output = $_elem->html().PHP_EOL.$output;
        }

        if ($appendages = $element->getAppendages('append'))
        {
            $output .= PHP_EOL;
            foreach ($appendages as $_elem)
                $output = $output.$_elem->html().PHP_EOL;
        }

        if ($element->container)
        {
            $output = $this->makeElementHTML($element->container, $output);
        }
        return $output;
    }

    /**
     * @param Form $form
     * @return string
     */
    public function renderOpen(Form $form)
    {
        $method = Str::lower($form->method ?: 'post');
        $form->setAttr('method', Str::upper($method));

        $methodField = null;
        if($method !== 'get' || $method !== 'post')
        {
            $form->setAttr('method', 'POST');
            $methodField = $form->hidden('_method', $method, ['baseNames' => false]);
            $methodField .= PHP_EOL;
        }
        $tag = sprintf("<%s%s>\n", $form->tag, $form->getAttributes());
        $tag .= $methodField;
        return $tag;
    }

    /**
     * @param Form $form
     * @return string
     */
    public function renderClose(Form $form)
    {
        $tail = null;
        if ($form->csrfToken && isset($this->session))
        {
            $format = '<input type="hidden" name="%s" value="%s"></input>';
            $tail .= sprintf($format, '_token', $this->session->token());
            $tail .= PHP_EOL;
        }
        return sprintf("%s</%s>\n", $tail, $form->tag());
    }

    /**
     * @param Field $field
     * @return string
     */
    protected function makeFieldHTML(Field $field)
    {
        $field->setAttr('name', $field->name);
        if (!$field->getAttr('type'))
            $field->setAttr('type', $field->type);
        if (!$field->ignoreLabel && $field->label)
        {
            $label = Element::make(['slug' => 'label', 'value' => $field->label, 'tag' => 'label'],
                ['class' => 'field-label', 'for' => $field->name]);
            $field->prepend($label);
        }

        if (!$field->ignoreDescription && $field->description)
        {
            $desc = Element::make(['slug' => 'description', 'value' => $field->description], ['class' => 'field-description']);
            $field->append($desc);
        }

        $default = $field->value;
        $field->value = $this->getFieldValue($field->safeName(), $default);

        $this->setFieldErrorMessage($field);

        if ($checkable = $field->checkable)
        {
            $_func = 'get'.Str::studly($checkable).'FieldOptions';
            $_html = $this->{$_func}($field, $field->options, $field->value);
            $html = $this->makeElementHTML($field, $_html);
        }
        elseif (in_array($field->type, $this->formFields))
        {
            $field->setAttr('name', $field->name);
            $field->setAttr('value', $field->value);
            $html = sprintf("<%s%s></%s>", $field->tag, $field->getAttributes(), $field->tag);
        }
        else
        {
            $html = $this->makeElementHTML($field);
        }
        return $html;
    }

    /**
     * @param Form $form
     * @param null $html
     * @return string
     */
    protected function makeFormHTML(Form $form, $html = null)
    {
        if (is_null($html))
            $html = value($form->value);
        return $form->open().PHP_EOL.$html.PHP_EOL.$form->close();
    }

    /**
     * @param Element $element
     * @param null $html
     * @return string
     */
    protected function makeElementHTML(Element $element, $html = null)
    {
        if (is_null($html))
            $html = $element->value;
        $html = value($html);

        return sprintf("<%s%s>\n%s\n</%s>\n",
            $element->tag, $element->getAttributes(), $html, $element->tag);
    }

    /**
     * @param Field $field
     */
    protected function setFieldErrorMessage(Field $field)
    {
        if (!isset($this->session) || !$this->session->has('errors'))
            return;
        $errors = $this->session->get('errors');
        $_name = $field->slug;
        if (!$found = $errors->has($_name))
        {
            if ($_name = $field->safeName())
                $found = $errors->has($_name);
        }
        if ($found)
        {
            $errElem = $field->append(Element::make(['slug' => 'errMsg'], ['class' => 'error-msg']));
            $errElem->value = $errors->first($_name);
            if ($field->container)
                $field->container->addClass('has-error');
        }
    }

    /**
     * @param $name
     * @param null $default
     * @return string
     */
    public function getFieldValue($name, $default = null)
    {
        if (is_null($name) || !isset($this->session))
            return value($default);
        if ($this->session->hasOldInput($name))
            $result = $this->session->getOldInput($name);
        else
            $result = $default;
        return value($result);
    }

    public function fire($action, $type, $element)
    {
        if ($this->dispatcher)
        {
            $event = "form.{$action}:{$type}";
            $this->dispatcher->fire($event, [$element]);
        }
    }

    /**
     * @param \Illuminate\Session\Store $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return \Illuminate\Session\Store
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param \Illuminate\Events\Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

}