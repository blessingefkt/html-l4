<?php namespace Iyoworks\Html;


use Illuminate\Routing\UrlGenerator;
use Iyoworks\Html\Forms\Field;
use Iyoworks\Html\Forms\ElementRendererInterface;

class FormBuilder extends \Illuminate\Html\FormBuilder {

    /**
     * @var \Iyoworks\Html\HtmlBuilder
     */
    protected $html;
    /**
     * @var ElementRendererInterface
     */
    private $renderer;

    public function __construct(ElementRendererInterface $renderer, HtmlBuilder $html, UrlGenerator $url, $csrfToken)
    {
        parent::__construct($html, $url, $csrfToken);
        $this->renderer = $renderer;
    }

    /**
     * @param  string $name
     * @param null $value
     * @param  array $options
     * @return string
     */
    public function passphrase($name, $value = null, $options = array())
    {
        return $this->input('password', $name, $value, $options);
    }

    /**
     * @param string $label
     * @param array $atts
     * @return string
     */
    public function button($label = null, $atts = [])
    {
        return $this->html->button($label, $atts, 'button', true);
    }

    /**
     * @param $label
     * @param string|array|null $atts
     * @return string
     */
    public function searchBtn($label, $atts = null)
    {
        $atts = $this->html->parseAtts($atts, 'btn btn-default btn-sm');
        $atts = array_merge(['icon' => 'fa-search'], $atts);
        return $this->html->button($label, $atts);
    }

    public function delete($route, array $atts = [])
    {
        $atts['class'] = 'btn btn-danger btn-sm';
        return $this->html->delete($route, 'Delete', $atts);
    }

    public function pfile($name, $label = null, $value = null, $classes = null)
    {
        return sprintf('%s'.PHP_EOL
            .'<div class="">'
            .'%s'.PHP_EOL
            .'<label data-title="%s" for="%s">'.PHP_EOL
            .'<span data-title="%s"></span>'.PHP_EOL
            .'</label>'.PHP_EOL
            .'</div>'.PHP_EOL,
            $this->label($name, $label, ['class' => 'control-label']),
            $this->file($name, ['id' => $name, 'class' => trim('content '.$classes)]),
            ($value) ? 'Change File' : 'Select File', $name,
            $value ?: 'No file selected...');
    }

    /**
     * @param $name
     * @param null $label
     * @param null $value
     * @param array $properties
     * @return \Iyoworks\Html\Forms\Field
     */
    public function field($name, $label = null, $value = null, array $properties = [])
    {
        $properties['slug'] = $name;
        $properties['name'] = $name;
        $properties['label'] = $label;
        $properties['value'] = $value;
        return Field::make($properties, [], $this->renderer);
    }

    public function urlSelect($label, $query, array $options, $baseUrl = null)
    {
        $html = '<label class="control-label">'.$label.'</label>'
            .'<select class="form-control url-select"'
            .'data-base-url="'.$baseUrl.'" data-query-param="'.$query.'">';
        foreach ($options as $queryValue => $option) {
            $selected = ($this->getCurrentParam($query) == $queryValue) ? 'selected="selected"': '';
            $html .= '<option '.$selected.' value="'.$queryValue.'">';
            $html .= $option;
            $html .= '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    protected function getCurrentParam($query)
    {
        return \Input::get($query);
    }
}