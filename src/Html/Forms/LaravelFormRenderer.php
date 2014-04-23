<?php namespace Iyoworks\Html\Forms;

use Illuminate\Http\Request;
use Illuminate\Session\Store as Session;
use Iyoworks\Support\Str;

class LaravelFormRenderer extends BaseElementRenderer implements FormRendererInterface  {
    /**
     * @var int
     */
    protected $maxColumns = 12;
    protected $formFields = ['checkbox','file','hidden','password','radio','reset','submit','text'];
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Request
     */
    protected $request;

    function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;
    }


    /**
     * @param Element $element
     * @param string $elementType
     * @return string
     */
    public function render(Element $element, $elementType)
    {
        switch($elementType)
        {
            case 'field':
                $output = $this->makeFieldHTML($element);
                break;
            default :
                $output = $this->makeElementHTML($element);
        }

        foreach ($element->getAppendages('prepend') as $_elem) {
            $output = $_elem->html().PHP_EOL.$output;
        }

        if ($appendages = $element->getAppendages('append'))
        {
            $output .= PHP_EOL;
            foreach ($appendages as $_elem)
                $output = $output.$_elem->html().PHP_EOL;
        }
        return $output;
    }

    /**
     * @param Form $form
     * @return string
     */
    public function renderOpen(Form $form)
    {
        $form->runCallbacks('form');
        $method = Str::lower($form->method ?: 'post');
        $form->set('method', Str::upper($method));

        if($method !== 'get' || $method !== 'post')
        {
            $form->hidden('_method', $method, ['baseNames' => false]);
            $form->set('method', 'POST');
        }

        return sprintf("<%s%s>\n", $form->tag(), $this->makeAttributeString($form));
    }

    /**
     * @param Form $form
     * @return string
     */
    public function renderClose(Form $form)
    {
        $tail = null;
        if ($form->csrfToken)
        {
            $tail = $form->hidden('_token', $this->session->token())->html();
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
        $field->set('name', $field->getProperty('name'));
        $field->set('type', $field->getProperty('type'));
        if ($multiple = $field->isProperty('multiple'))
            $field->set('multiple', $multiple);

        $field->set('value', $this->getFieldValue($field->dotName(), $field->getProperty('value')));

        if ($desc = $field->description)
        {
            $desc = Element::make(['slug' => 'description', 'value' => $desc], ['class' => 'field-description']);
            $field->append($desc);
        }

        $this->setFieldErrorMessage($field);

        $attributeStr = $this->makeAttributeString($field->toArray());

        if (in_array($field->type, $this->formFields))
        {
            $html = sprintf("<%s%s></%s>\n", $field->tag,$attributeStr, $field->tag);
        }
        else
            $html = $this->makeElementHTML($field);

        if ($container = $field->container)
        {
            if (is_array($container))
            {
                return $this->makeElementHTML(Element::make(['value' => $html], $container));
            }
            return $this->makeElementHTML($field->container, $html);
        }
        return $html;
    }

    protected function makeElementHTML(Element $element, $html = null)
    {
        return sprintf("<%s%s>\n%s\n</%s>\n",
            $element->tag(), $element->getAttributes(), $html ?: value($element->value), $element->tag());
    }

    /**
     * @param Field $field
     */
    protected function setFieldErrorMessage(Field $field)
    {
        if ($this->session->has('errors')) {
            $errors = $this->session->get('errors');
            $_name = $field->slug;
            if (!$found = $errors->has($_name)) {
                if ($_name = $field->dotName())
                    $found = $errors->has($_name);
            }
            if ($found) {
                $errElem = $field->append(Element::make(['slug' => 'errMsg'], ['class' => 'has-error']));
                $errElem->value = $errors->first($_name);
            }
        }
    }

    /**
     * @param $name
     * @param null $default
     * @return string
     */
    public function getFieldValue($name, $default = null)
    {
        if ($this->session->has($name))
            return $this->session->get($name);
        if ($this->request->has($name))
            return $this->request->input($name);
        return value($default);
    }
}