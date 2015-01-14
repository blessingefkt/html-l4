<?php namespace Iyoworks\Html;

class BreadCrumbs {

	/**
	 * @var array
	 */
	protected $crumbs = [];
	/**
	 * @var \Iyoworks\Html\HtmlBuilder
	 */
	private $builder;

	public function __construct(HtmlBuilder $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * @param $title
	 * @param null $url
	 * @param array $attributes
	 * @return $this
	 */
	public function add($title, $url = null, array $attributes = [])
	{
		if (is_array($title))
		{
			foreach ($title as $_title => $_url)
			{
				$this->crumbs[] = [$_title, $_url, $attributes];
			}
		}
		else
		{
			$this->crumbs[] = [$title, $url, $attributes];
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function process()
	{
		$crumbs = '';
		foreach ($this->crumbs as $crumb)
		{
			list($title, $url, $attributes) = $crumb;
			if ($url) $title = $this->builder->link($url, $title, $attributes);
			$crumbs .= "<span>{$title}</span>";
		}
		return $crumbs;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->process();
	}


} 