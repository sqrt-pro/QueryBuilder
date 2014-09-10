<?php

namespace SQRT\QueryBuilder\Query;

use SQRT\QueryBuilder\Query;

class Insert extends Query
{
  protected $on_duplicate_key_update;

  /** Создать SQL */
  public function asSQL()
  {
    $q = 'INSERT INTO ' . $this->prepareTable()
      . $this->prepareSet()
      . $this->prepareOnDuplicateKeyUpdate();

    return $this->processBindedVarsToSQL($q);
  }

  /** Создать выражение с подстановкой переменных */
  public function asStatement()
  {
    return 'INSERT INTO ' . $this->prepareTable()
    . $this->prepareSet(false)
    . $this->prepareOnDuplicateKeyUpdate(false);
  }

  /** Список переменных для подстановки */
  public function getBindedValues()
  {
    return $this->processSetBindedVars($this->values ? : null);
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

  public function setOnDuplicateKeyUpdate($on_duplicate_key_update = true)
  {
    $this->on_duplicate_key_update = $on_duplicate_key_update;

    return $this;
  }

  public function getOnDuplicateKeyUpdate()
  {
    return $this->on_duplicate_key_update;
  }

  protected function prepareOnDuplicateKeyUpdate($as_sql = true)
  {
    $str = $this->on_duplicate_key_update ? $this->prepareSetValues($as_sql) : false;

    return !empty($str) ? ' ON DUPLICATE KEY UPDATE ' . $str : '';
  }
}