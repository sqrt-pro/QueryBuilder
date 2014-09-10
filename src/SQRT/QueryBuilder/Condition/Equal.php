<?php

namespace SQRT\QueryBuilder\Condition;

use SQRT\QueryBuilder\Condition;
use SQRT\QueryBuilder\Query;

class Equal extends Condition
{
  function __construct($column, $value, $not = null)
  {
    $this->column = $column;
    $this->values = $value;
    $this->not    = $not;
  }

  public function asSQL()
  {
    if (is_null($this->values)) {
      return $this->isNull();
    }

    return Query::QuoteKey($this->column) . ($this->not ? '!=' : '=') . Query::QuoteValue($this->values);
  }

  public function asStatement($prefix = null)
  {
    if (is_null($this->values)) {
      return $this->isNull();
    }

    return Query::QuoteKey($this->column) . ($this->not ? '!=' : '=') . Query::Placeholder($this->column, $prefix);
  }

  public function getBindedValues($prefix = null)
  {
    if (is_null($this->values)) {
      return false;
    }

    return array(Query::Placeholder($this->column, $prefix) => $this->values);
  }

  protected function isNull()
  {
    return Query::QuoteKey($this->column) . ' ' . ($this->not ? 'IS NOT NULL' : 'IS NULL');
  }
}