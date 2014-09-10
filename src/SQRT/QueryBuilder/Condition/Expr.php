<?php

namespace SQRT\QueryBuilder\Condition;

use SQRT\QueryBuilder\Condition;
use SQRT\QueryBuilder\Query;

class Expr extends Condition
{
  protected $expr;

  function __construct($expr, $values = null)
  {
    $this->expr   = $expr;
    $this->values = $values;
  }

  public function asSQL()
  {
    $str = $this->expr;
    if ($arr = $this->getBindedValues()) {
      foreach ($arr as $key => $val) {
        $str = str_replace($key, Query::QuoteValue($val), $str);
      }
    }

    return $str;
  }

  public function asStatement($prefix = null)
  {
    return $this->expr;
  }

  public function getBindedValues($prefix = null)
  {
    if (empty($this->values)) {
      return false;
    }

    $arr = array();
    foreach ($this->values as $key => $val) {
      if (is_numeric($key)) {
        $key = '_' . $key;
      }

      $arr[Query::Placeholder($key)] = $val;
    }

    return $arr;
  }
}