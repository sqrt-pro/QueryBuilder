<?php

namespace SQRT\QueryBuilder\Condition;

use SQRT\QueryBuilder\Condition;
use SQRT\QueryBuilder\Query;

class Like extends Condition
{
  protected $date_format;

  function __construct($column, $value, $not = null)
  {
    $this->column = $column;
    $this->values = $value;
    $this->not    = $not;
  }

  public function asSQL()
  {
    return sprintf(
      '%s %sLIKE %s',
      Query::QuoteKey($this->column),
      ($this->not ? 'NOT ' : ''),
      Query::QuoteValue($this->values)
    );
  }

  public function asStatement($prefix = null)
  {
    return sprintf(
      '%s %sLIKE %s',
      Query::QuoteKey($this->column),
      ($this->not ? 'NOT ' : ''),
      Query::Placeholder($this->column, $prefix)
    );
  }

  public function getBindedValues($prefix = null)
  {
    return array(Query::Placeholder($this->column, $prefix) => $this->values);
  }
}