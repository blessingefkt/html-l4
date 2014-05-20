<?php namespace Iyoworks\Html\Tables;


use Illuminate\Support\Fluent;

class Element extends \Iyoworks\Html\Element {
	/**
	 * @var string
	 */
	public $label;

	protected $format;
	protected $tag = 'td';

	function __construct($label = null, array $attributes = array(), array $properties = array())
	{
		$this->label = $label;
		parent::__construct($attributes, $properties);
	}

	/**
	 * @param array $array
	 * @return $this
	 */
	public function fill(array $array)
	{
		$this->attributes = $array;
		return $this;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->attributes[$key]);
	}

	/**
	 * @return string
	 */
	public function getAttributeString()
	{
		return \HTML::attributes($this->attributes);
	}

	/**
	 * @param $label
	 * @return $this|string
	 */
	public function label($label = null)
	{
		if (!$label) return $this->label;
		$this->label = $label;
		return $this;
	}

	/**
	 * @param $format
	 * @return $this|string
	 */
	public function format($format = null)
	{
		if (!$format) return $this->format;
		$this->format = $format;
		return $this;
	}

	protected function formatHTMl($html)
	{
		return sprintf($this->format, $this->getAttributeString(), $html);
	}


} 