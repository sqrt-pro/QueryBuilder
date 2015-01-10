<?php

namespace SQRT\QueryBuilder\Condition;

use SQRT\QueryBuilder\Condition;
use SQRT\QueryBuilder\Query;

class Less extends Condition
{
  protected $or_equal;

  function __construct($column, $value, $or_equal = null)
  {
    $this->column   = $column;
    $this->values   = $value;
    $this->or_equal = $or_equal;
  }

  public function asSQL()
  {
    return Query::QuoteKey($this->column) . ($this->or_equal ? '<=' : '<') . Query::QuoteValue($this->values);
  }

  public function asStatement($prefix = null)
  {
    return Query::QuoteKey($this->column) . ($this->or_equal ? '<=' : '<') . Query::Placeholder($this->column . '_lt', $prefix);
  }

  public function getBindedValues($prefix = null)
  {
    return array(Query::Placeholder($this->column . '_lt', $prefix) => $this->values);
  }

  /** @return static */
  public function setOrEqual($or_equal = true)
  {
    $this->or_equal = $or_equal;

    return $this;
  }

  public function getOrEqual()
  {
    return $this->or_equal;
  }
}