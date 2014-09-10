<?php

namespace SQRT\QueryBuilder\Query;

class Delete extends FilteredQuery
{
  /** Создать SQL */
  public function asSQL()
  {
    $q = 'DELETE FROM ' . $this->prepareTable()
      . $this->prepareWhere()
      . $this->prepareOrderBy()
      . $this->prepareLimit();

    return $this->processBindedVarsToSQL($q);
  }

  /** Создать выражение с подстановкой переменных */
  public function asStatement()
  {
    return 'DELETE FROM ' . $this->prepareTable()
    . $this->prepareWhere(false)
    . $this->prepareOrderBy()
    . $this->prepareLimit();
  }

  /** Список переменных для подстановки */
  public function getBindedValues()
  {
    return $this->processWhereBindedVars($this->values ? : null);
  }
}