<?php

namespace SQRT\QueryBuilder\Condition;

use SQRT\QueryBuilder\Condition;
use SQRT\QueryBuilder\Query;

class In extends Condition
{
  function __construct($column, array $array, $not = null)
  {
    $this->column = $column;
    $this->values = $array;
    $this->not    = $not;
  }

  public function asSQL()
  {
    $arr = array();
    foreach ($this->values as $key => $val) {
      $arr[] = Query::QuoteValue($val);
    }

    return Query::QuoteKey($this->column) . ($this->not ? ' NOT' : '') . ' IN (' . join(', ', $arr) . ')';
  }

  public function asStatement($prefix = null)
  {
    $arr = array();
    $i = 1;
    foreach ($this->values as $val) {
      $arr[] = Query::Placeholder($this->column . '_' . $i++, $prefix);
    }

    return Query::QuoteKey($this->column) . ($this->not ? ' NOT' : '') . ' IN (' . join(', ', $arr) . ')';
  }

  public function getBindedValues($prefix = null)
  {
    $arr = array();
    $i = 1;
    foreach ($this->values as $val) {
      $arr[Query::Placeholder($this->column . '_' . $i++, $prefix)] = $val;
    }

    return $arr;
  }
}