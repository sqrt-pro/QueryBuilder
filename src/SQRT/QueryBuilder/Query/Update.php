<?php

namespace SQRT\QueryBuilder\Query;

use SQRT\QueryBuilder\Query;

class Update extends FilteredQuery
{
  /** Создать SQL */
  public function asSQL()
  {
    $q = 'UPDATE ' . $this->prepareTable()
      . $this->prepareSet()
      . $this->prepareWhere()
      . $this->prepareOrderBy()
      . $this->prepareLimit();

    return $this->processBindedVarsToSQL($q);
  }

  /** Создать выражение с подстановкой переменных */
  public function asStatement()
  {
    return 'UPDATE ' . $this->prepareTable()
    . $this->prepareSet(false)
    . $this->prepareWhere(false)
    . $this->prepareOrderBy()
    . $this->prepareLimit();
  }

  /** Список переменных для подстановки */
  public function getBindedValues()
  {
    $vars = $this->processWhereBindedVars($this->values ? : null);
    $vars = $this->processSetBindedVars($vars);

    return $vars;
  }

  public function setEqual($column, $value)
  {
    return parent::setEqual($column, $value);
  }

  public function setExpr($expr)
  {
    return parent::setExpr($expr);
  }

  public function setFromArray(array $array)
  {
    return parent::setFromArray($array);
  }
}