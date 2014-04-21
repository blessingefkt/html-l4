<?php namespace Iyoworks\Html\Tables;

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
     * @param callable $builder
     */
    public function setBuilder(callable $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return mixed
     */
    protected function buildTable()
    {
        return call_user_func($this->builder, $this);
    }


} 