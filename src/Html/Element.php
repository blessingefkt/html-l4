<?php namespace Iyoworks\Html;

use Illuminate\Support\Str;

/**
 * Class Element
 */
class Element {
	protected $attributes = array();
	protected $properties = array();

	/**
	 * @param array $attributes
	 * @param array $properties
	 */
	public function __construct(array $attributes = [], array $properties = [])
	{
		$this->mergeAttributes($attributes);
		$this->setProperties($properties);
	}

	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function set($key, $value)
	{
		if ($this->isProperty($key))
		{
			$this->setProperty($key, $value);
		}
		else
		{
			$this->setAttr($key, $value);
		}
		return $this;
	}

	/**
	 * Get an attribute from the container.
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if ($this->isProperty($key))
		{
			$value = $this->getProperty($key, $default);
		}
		else
		{
			$value = $this->getAttr($key, $default);
		}

		return $value;
	}

	/**
	 * @param array $properties
	 * @return $this
	 */
	public function setProperties(array $properties)
	{
		foreach ($properties as $name => $value)
		{
			$this->setProperty($name, $value);
		}
		return $this;
	}

	/**
	 * @param $properties
	 * @return $this
	 */
	public function appendProperties($properties)
	{
		$this->properties = array_merge($this->properties, $properties);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @param $name
	 * @param $value
	 * @return $this
	 */
	public function setProperty($name, $value)
	{
		if (method_exists($this, $method = 'onSet' . Str::studly($name)))
		{
			$this->{$method}($value);
		}
		else
		{
			$this->properties[$name] = $value;
		}
		return $this;
	}

	/**
	 * @param $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getProperty($name, $default = null)
	{
		if (!$this->hasProperty($name))
		{
			$value = $default;
		}
		else
		{
			$value = $this->properties[$name];
		}
		$value = value($value);

		if (method_exists($this, $method = 'onGet' . Str::studly($name)))
		{
			return $this->{$method}($value);
		}
		return $value;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function removeProperty($name)
	{
		unset($this->properties[$name]);
		return $this;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function isProperty($key)
	{
		return array_key_exists($key, $this->properties);
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function hasProperty($key)
	{
		return isset($this->properties[$key]);
	}

	/**
	 * @param array $attributes
	 * @return $this
	 */
	public function mergeAttributes(array $attributes)
	{
		$this->attributes = array_merge_recursive($this->attributes, $attributes);
		return $this;
	}

	/**
	 * @param $key
	 * @param null $default
	 * @return mixed
	 */
	public function getAttr($key, $default = null)
	{
		if (array_key_exists($key, $this->attributes))
		{
			$value = $this->attributes[$key];
		}
		else
		{
			$value = $default;
		}
		return value($value);
	}

	/**
	 *
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function setAttr($key, $value)
	{
		$this->attributes[$key] = $value;
		return $this;
	}

	/**
	 * @param $k
	 * @param $v
	 * @return $this
	 */
	public function setData($k, $v)
	{
		return $this->setAttr('data-' . $k, $v);
	}

	/**
	 * @param $k
	 * @param $v
	 * @return mixed
	 */
	public function getData($k, $v)
	{
		return $this->getAttr('data-' . $k, $v);
	}

	/**
	 * Add a class
	 * @param $class
	 * @return $this
	 */
	public function addClass($class)
	{
		if (is_array($class))
		{
			array_map([$this, 'addClass'], $class);
		}
		else
		{
			$classes = (array)array_get($this->attributes, 'class', []);
			if (!is_array($class))
			{
				$class = explode(' ', $class);
			}
			$classes = array_merge($classes, $class);
			$this->attributes['class'] = array_unique($classes);
		}
		return $this;
	}

	/**
	 * @param $class
	 * @return bool
	 */
	public function hasClass($class)
	{
		return in_array($class, array_get($this->attributes, 'class', []));
	}

	/**
	 * Remove a class
	 * @param $class
	 * @return $this
	 */
	public function removeClass($class)
	{
		if (is_array($class))
		{
			array_map([$this, 'removeClass'], $class);
		}
		else
		{
			$classes = (array)array_get($this->attributes, 'class', []);
			if ($key = array_search($class, $classes))
			{
				unset($classes[$key]);
			}
			$this->attributes['class'] = $classes;
		}
		return $this;
	}

	/**
	 * @param array $attributes
	 * @return $this
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		$attributes = $this->attributes;
		$class = array_pull($attributes, 'class');
		if (is_array($class))
		{
			$attributes['class'] = join(' ', $class);
		}
		elseif (is_string($class))
		{
			$attributes['class'] = $class;
		}
		return $attributes;
	}

	/**
	 * Return a property or attribute
	 *
	 * @param $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Set a property or attribute
	 *
	 * @param $name
	 * @param $value
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Lets us add custom field settings to be used during the render process.
	 *
	 * @param  string $name Setting name
	 * @param  array $arguments Setting value(s)
	 *
	 * @return $this
	 */
	public function __call($name, $arguments)
	{
		if (!sizeof($arguments))
		{
			$this->set($name, true);
		}
		elseif ($name == 'class')
		{
			$this->addClass($arguments);
		}
		elseif (sizeof($arguments) == 1)
		{
			$this->set($name, $arguments[0]);
		}
		return $this;
	}
}
