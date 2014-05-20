<?php namespace Iyoworks\Html\Tables;


class Cell extends Element {
	/**
	 * @var string
	 */
	protected $format = '<%s %s>%s</%s>';
	/**
	 * @var string
	 */
	public $label;

	public function __construct($label, array $attributes = null)
	{
		parent::__construct($label, $attributes ? : []);
	}

	/**
	 * @param $tag
	 * @return $this|string
	 */
	public function tag($tag = null)
	{
		if (!$tag) return $this->tag;
		$this->tag = $tag;
		return $this;
	}

	public function render($html = null)
	{
		return sprintf($this->format, $this->tag, $this->getAttributeString(), $html ? : $this->label, $this->tag);
	}
} 