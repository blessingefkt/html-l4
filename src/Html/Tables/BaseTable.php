<?php namespace Iyoworks\Html\Tables;

abstract class BaseTable extends Element implements \Countable {
    /**
     * @var Cell[]
     */
    protected $cells = [], $headers = [];
    /**
     * @var string
     */
    protected $headerKey = '_headers';
    /**
     * @var string
     */
    protected $format = '<table %s>
        <thead>
        <tr>%s</tr>
        </thead>
        <tbody> %s </tbody>
    </table>';
    /**
     * @var bool
     */
    protected $datatable = true;
    /**
     * @var string
     */
    protected $emptyMsg = '<h1>Looks like there are no records to display.</h1>';

    function __construct($label = null, array $attributes = array())
    {
        parent::__construct($label, $attributes);
        $this->addClass('table table-striped');
    }


    abstract protected function buildTable();

    /**
     * @param string $value
     * @param string $slug
     * @return Cell
     */
    public function header($slug, $value = null)
    {
        return $this->cells[$this->headerKey][$slug] = new Cell($value ?: $slug);
    }

    /**
     * @param $row
     * @param $column
     * @param Cell|string $value
     * @internal param $column
     * @return Cell
     */
    public function cell($row, $column, $value)
    {
        if ($value instanceof Cell)
            $cell = $value;
        else
            $cell = $this->makeCell($value);
        return $this->cells[$row][$column] = $cell;
    }

    /**
     * @param $model
     * @param $column
     * @param Cell|string $value
     * @return Cell
     */
    public function entity($model, $column, $value)
    {
        return $this->cell($model->id, $column, $value);
    }

    /**
     * @param $row
     * @param $column
     * @return Cell
     */
    public function getCell($row, $column)
    {
        if (isset($this->cells[$row][$column]))
            return $this->cells[$row][$column];
        return  $this->cell($row, $column, null);
    }

    /**
     * @param $value
     * @param null $attributes
     * @return Cell
     */
    public function makeCell($value, $attributes = null)
    {
        return new Cell($value, $attributes);
    }

    public function render()
    {
        $this->buildTable();

        if ($this->isEmpty() && isset($this->emptyMsg))
            return $this->emptyMsg;

        $html = null;
        $cells = $this->cells;

        if ($headers = $this->renderHeaders())
            array_forget($cells, $this->headerKey);

        $html = $this->renderCells($cells, array_get($this->cells, $this->headerKey, null));

        if ($this->datatable)
            $this->attributes['data-datatable'] = 'true';
        return sprintf($this->format, $this->getAttributes(),
            $headers,
            $html);
    }

    /**
     * @param $str
     * @param array $attributes
     * @return string
     */
    protected function formatRow($str, $attributes = null)
    {
        return sprintf('<tr %s>%s</tr>', $this->makeAttrString($attributes), $str);
    }

    /**
     * @param $str
     * @param array $attributes
     * @return string
     */
    protected function formatCell($str, $attributes = [])
    {
        return sprintf('<td %s>%s</td>', $this->makeAttrString($attributes), $str);
    }

    /**
     * @param $str
     * @param array $attributes
     * @return string
     */
    protected function formatHeaderCell($str, $attributes = [])
    {
        return sprintf('<th %s>%s</th>', $this->makeAttrString($attributes), $str);
    }

    protected function makeAttrString(array $attributes = null)
    {
        if ($attributes) return \HTML::attributes($attributes);
    }

    /**
     * @return null|string
     */
    public function renderHeaders()
    {
        $html = null;
        if ($headers = array_get($this->cells, $this->headerKey, null))
        {
            $_html = null;
            foreach ($headers as $slug => $cell) {
                $_html .= $cell->tag('th')->render();
            }
            $html = $this->formatRow($_html);
        }
        return $html;
    }

    /**
     * @param Cell[] $cells
     * @param Cell[] $headers
     * @return string
     */
    public function renderCells(array $cells = null, array $headers = null)
    {
        if (is_null($cells)) $cells = $this->cells;
        $html = null;
        foreach ($cells as $row => $columns) {
            $_html = null;
            if ($headers)
            {
                foreach ($headers as $slug => $header) {
                    if ($cell = array_get($columns, $slug, null))
                        $_html .= $cell->render();
                }
                $html .= $this->formatRow($_html, $this->rowAttrs);
            }
            else
            {
                foreach ($columns as $column => $cell) {
                    $_html .= $cell->render();
                }
                $html .= $this->formatRow($_html, $this->rowAttrs);
            }
        }
        return $html;
    }

    /**
     * @param string $string
     * @return $this
     */
    public function emptyMsg($string)
    {
        $this->emptyMsg = $string;
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
       return count($this->cells, COUNT_RECURSIVE);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->cells);
    }

    public function __toString()
    {
        return $this->render();
    }
} 