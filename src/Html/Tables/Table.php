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
	protected $builder;
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
	 * @param null $label
	 * @param array $attributes
	 * @return \Iyoworks\Html\Tables\Table
	 */
	public static function make($label = null, array $attributes = [])
	{
		return new static($label, $attributes);
	}

	/**
	 * @return mixed
	 */
	protected function buildTable()
	{
		return call_user_func($this->builder, $this);
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
			$this->header(is_int($k) ? Str::slug($header, '_') : $k, $header);
		}
		return $this;
	}

	/**
	 * @param callable $build
	 * @return $this
	 */
	public function builder(callable $build)
	{
		$this->builder = function ($table) use ($build)
		{
			foreach ($table->items as $key => $item)
			{
				call_user_func($build, $table, $item, $key);
			}
		};
		return $this;
	}
} 