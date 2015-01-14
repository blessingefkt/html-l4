<?php namespace Iyoworks\Html;

/**
 * Class HtmlBuilder
 * @package Tespa\Service
 */
class HtmlBuilder extends \Illuminate\Html\HtmlBuilder {

	/**
	 * @param $label
	 * @param $url
	 * @param null $atts
	 * @return string
	 */
	public function submitLink($label, $url, $atts = null)
	{
		return $this->btnLink($label, $url, $atts, true);
	}

	/**
	 * @param $label
	 * @param $url
	 * @param string|array|null $atts
	 * @param bool $submit
	 * @return string
	 */
	public function btnLink($label, $url, $atts = null, $submit = false)
	{
		$atts = $this->parseAtts($atts, 'btn btn-sm');
		if ($submit || isset($atts['data-method']))
		{
			$atts['data-submit'] = $url;
		}
		$atts['href'] = $url;
		return $this->button($label, $atts, 'a');
	}

	/**
	 * @param string $url
	 * @param null|string $title
	 * @param array $attributes
	 * @param bool $secure
	 * @return string
	 */
	public function link($url, $title = null, $attributes = [], $secure = false)
	{
		$url = $this->url->to($url, array(), $secure);

		if (is_null($title) || $title === false) $title = $url;
		$attributes = $this->parseAtts($attributes);
		if ($icon = array_pull($attributes, 'icon'))
		{
			$icon = "<i class=\"fa {$icon}\"></i> ";
		}

		if (isset($attributes['data-method']))
		{
			$attributes['data-submit'] = $url;
		}

		$prepend = array_pull($attributes, 'prepend');
		$append = array_pull($attributes, 'append');

		return '<a href="' . $url . '"' . $this->attributes($attributes) . '>' . $prepend .
		$icon . $this->entities($title) . $append. '</a>';
	}

	/**
	 * @param $label
	 * @param string|array|null $atts
	 * @param string $tag
	 * @param bool $submit
	 * @return string
	 */
	public function button($label, $atts = null, $tag = 'button', $submit = false)
	{
		$atts = $this->parseAtts($atts, 'btn btn-sm');
		if ($icon = array_pull($atts, 'icon'))
		{
			$icon = "<i class=\"fa {$icon}\"></i> ";
		}
		if ($submit) $atts['type'] = 'submit';
		return sprintf("<%s%s>%s%s</%s>", $tag, $this->attributes($atts), $icon, $label, $tag);
	}

	/**
	 * @param $url
	 * @param string $label
	 * @param array $atts
	 * @param bool $secure
	 * @return string
	 */
	public function delete($url, $label = 'Delete', array $atts = null, $secure = false)
	{
		if (is_array($label))
		{
			$atts = $label;
			$label = 'Delete';
		}
		$atts['data-method'] = 'delete';
		if (!isset($atts['icon'])) $atts['icon'] = 'fa-trash-o';
		return $this->link($url, $label, $atts, $secure);
	}

	/**
	 * @param \Tespa\Model\Model $model
	 * @param array $btns
	 * @param bool $override
	 * @return string
	 */
	public function modelBtns($model, array $btns = [], $override = false)
	{
		if (!$override)
		{
			$btns['Edit'] = [$model->url('edit'), 'fa-edit', 'btn-default'];
			$btns['Delete'] = [$model->url('delete'), 'fa-trash-o', 'btn-danger', true, ['data-method' => 'delete']];
		}
		$html = null;
		foreach ($btns as $label => $btn)
		{
			$url = array_get($btn, 0, null);
			$atts['icon'] = array_get($btn, 1, null);
			$atts['class'] = trim('btn ' . array_get($btn, 2, null));
			if ($submit = array_get($btn, 3, null))
			{
				if (is_array($submit))
				{
					$atts = array_merge($atts, $submit);
					$submit = false;
				}
				elseif (is_string($submit))
				{
					$atts['data-method'] = $submit;
					$submit = true;
				}
			}
			if (is_array($moreAtts = array_get($btn, 4, [])))
			{
				$atts = array_merge($atts, $moreAtts);
			}
			$html .= $this->btnLink($label, $url, $atts, $submit);
		}
		return sprintf('<div class="btn-group btn-group-sm">%s</div>', $html);
	}

	/**
	 * @param $atts
	 * @param null $default
	 * @return array
	 */
	public function parseAtts($atts, $default = null)
	{
		if (is_null($atts))
		{
			$atts['class'] = $default;
		}
		elseif (is_string($atts))
		{
			$atts = ['class' => $atts];
		}
		else
		{
			$atts = (array)$atts;
			if (!array_key_exists('class', $atts))
			{
				$atts['class'] = $default;
			}
		}
		return $atts;
	}
}