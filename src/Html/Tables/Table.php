<?php namespace Iyoworks\Html\Tables;

use Iyoworks\Support\Str;

/**
 * Class Table
 * @package Iyoworks\Html\Tables
 */
class Table extends BaseTable {

	/**
	 * @var callable
	 */
	protected $builder, $rowBuilder;
	/**
	 * @var array|\ArrayAccess|\ArrayIterator
	 */
	public $items;

	/**
	 * @param string $label
	 * @param array $attributes
	 */
	function __construct($label = null, array $attributes = [])
	{
		parent::__construct($label, $attributes);
	}

	/**
	 * @param array $items
	 * @param null $label
	 * @param array $attributes
	 * @return \Iyoworks\Html\Tables\Table
	 */
	public static function make($items = null, $label = null, array $attributes = [])
	{
		$table = new static($label, $attributes);
		if ($items) $table->setItems($items);
		return $table;
	}

	/**
	 * @return mixed
	 */
	protected function buildTable()
	{
		if ($this->builder)
		{
			call_user_func($this->builder, $this);
		}
		if ($this->rowBuilder)
		{
			foreach ($this->items as $key => $item)
			{
				call_user_func($this->rowBuilder, $this, $item, $key);
			}
		}
	}

	/**
	 * @param array $items
	 * @return $this
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}

	/**
	 * @param array $headers
	 * @return $this
	 */
	public function setHeaders(array $headers)
	{
		foreach ($headers as $k => $header)
		{
			if ($header instanceof \Closure)
			{
				$headerCell = $this->header($k);
				call_user_func($header, $headerCell);
			}
			else
			{
				$this->header(is_int($k) ? Str::slug($header, '_') : $k, $header);
			}
		}
		return $this;
	}

	/**
	 * @param callable $build
	 * @return $this
	 */
	public function builder(callable $build)
	{
		$this->builder = $build;
		return $this;
	}

	/**
	 * @param callable $build
	 * @return $this
	 */
	public function rowBuilder(callable $build)
	{
		$this->rowBuilder = $build;
		return $this;
	}
} 