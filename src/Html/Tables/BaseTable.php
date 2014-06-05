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
	protected $datatable = false, $skipHeaders = false;
	/**
	 * @var string
	 */
	protected $emptyMsg = '<h1>Looks like there are no records to display.</h1>';

	protected $emptyMessageCallback;

	abstract protected function buildTable();

	/**
	 * @param string $value
	 * @param string $slug
	 * @return Cell
	 */
	public function header($slug, $value = null)
	{
		return $this->cells[$this->headerKey][$slug] = new Cell(is_null($value) ? $slug : $value);
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
		{
			$cell = $value;
		}
		else
		{
			$cell = $this->makeCell($value);
		}
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
		{
			return $this->cells[$row][$column];
		}
		return $this->cell($row, $column, null);
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

		$html = null;
		$headers = null;

		if (!$this->skipHeaders)
		{
			$headers = $this->renderHeaders();
		}


		if ($this->isEmpty() && isset($this->emptyMsg))
		{
			$cells = [$this->getEmptyMsgCell()];
		}
		else
		{
			$cells = $this->cells;
		}
		$html = $this->renderCells($cells, $this->getHeaderCells());

		if ($this->datatable)
		{
			$this->attributes['data-datatable'] = 'true';
		}
		return sprintf($this->format, $this->getAttributeString(), $headers, $html);
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
	 * @return Cell[]
	 */
	public function getHeaderCells()
	{
		return array_get($this->cells, $this->headerKey, []);
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
			foreach ($headers as $slug => $cell)
			{
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
		foreach ($cells as $row => $columns)
		{
			if ($row == $this->headerKey) continue;
			$_html = null;
			if (!empty($headers))
			{
				foreach ($headers as $slug => $header)
				{
					if ($cell = array_get($columns, $slug, null))
					{
						$_html .= $cell->render();
					}
				}
				$html .= $this->formatRow($_html, $this->rowAttrs);
			}
			else
			{
				foreach ($columns as $column => $cell)
				{
					$_html .= $cell->render();
				}
				$html .= $this->formatRow($_html, $this->rowAttrs);
			}
		}
		return $html;
	}

	/**
	 * @param null $msg
	 * @return Cell
	 */
	public function getEmptyMsgCell($msg = null)
	{
		$cell = new Cell($msg ? : $this->emptyMsg, ['colspan' => $this->headerCount()]);
		if (isset($this->emptyMessageCallback))
		{
			call_user_func($this->emptyMessageCallback, $cell);
		}
		return $cell;
	}

	/**
	 * @param boolean $skipHeaders
	 * @return $this
	 */
	public function skipHeaders($skipHeaders = true)
	{
		$this->skipHeaders = (bool)$skipHeaders;
		return $this;
	}

	/**
	 * @param boolean $datatable
	 * @return $this
	 */
	public function setDatatable($datatable = true)
	{
		$this->datatable = (bool)$datatable;
		return $this;
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
	 * @return int
	 */
	public function headerCount()
	{
		return count($this->getHeaderCells(), COUNT_RECURSIVE);
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		$cells = $this->cells;
		unset($cells[$this->headerKey]);
		return empty($cells);
	}

	/**
	 * @param callable $emptyMessageCallback
	 * @return $this
	 */
	public function setEmptyMsgCallback(callable $emptyMessageCallback)
	{
		$this->emptyMessageCallback = $emptyMessageCallback;
		return $this;
	}

	public function __toString()
	{
		return $this->render();
	}
} 