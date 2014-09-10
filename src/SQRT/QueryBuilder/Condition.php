<?php

namespace SQRT\QueryBuilder;

abstract class Condition
{
  protected $values;
  protected $not;

  function __toString()
  {
    return $this->asSQL();
  }

  abstract public function asSQL();

  abstract public function asStatement($prefix = null);

  abstract public function getBindedValues($prefix = null);

  public function setValues($values)
  {
    $this->values = $values;

    return $this;
  }

  public function getValues()
  {
    return $this->values;
  }

  /** Инверсия логического выражения NOT ... */
  public function setNot($not = true)
  {
    $this->not = (bool)$not;

    return $this;
  }

  /** Инверсия логического выражения NOT ... */
  public function getNot()
  {
    return (bool)$this->not;
  }
}