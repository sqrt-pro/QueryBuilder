<?php

namespace SQRT\QueryBuilder\Query;

use SQRT\QueryBuilder\Query;
use SQRT\QueryBuilder\Conditions;

class Select extends FilteredQuery
{
  /** @var Conditions */
  protected $having;
  protected $join;
  protected $columns;
  protected $groupby;

  /** Создать SQL */
  public function asSQL()
  {
    $q = 'SELECT ' . $this->prepareColumns() . ' FROM ' . $this->prepareTable()
      . $this->prepareJoin()
      . $this->prepareWhere()
      . $this->prepareGroupBy()
      . $this->prepareHaving()
      . $this->prepareOrderBy()
      . $this->prepareLimit();

    return $this->processBindedVarsToSQL($q);
  }

  /** Создать выражение с подстановкой переменных */
  public function asStatement()
  {
    return 'SELECT ' . $this->prepareColumns() . ' FROM ' . $this->prepareTable()
      . $this->prepareJoin()
      . $this->prepareWhere(false)
      . $this->prepareGroupBy()
      . $this->prepareHaving(false)
      . $this->prepareOrderBy()
      . $this->prepareLimit();
  }

  /** Список переменных для подстановки */
  public function getBindedValues()
  {
    $vars = $this->processWhereBindedVars($this->values ? : null);

    if ($this->having) {
      if ($arr = $this->getHaving()->getBindedValues('having')) {
        $vars = $vars ? array_merge($arr, $vars) : $arr;
      }
    }

    return $vars;
  }

  /** @return static */
  public function columns($mixed, $_ = null)
  {
    if (!is_array($mixed)) {
      $mixed = func_get_args();
    }

    $this->columns = $mixed;

    return $this;
  }

  /** @return static */
  public function join($table, $on, $type = null)
  {
    $this->join[$table] = array(
      'table' => $table,
      'on'    => $on,
      'type'  => $type,
    );

    return $this;
  }

  /** @return static */
  public function groupby($mixed, $_ = null)
  {
    if (!is_array($mixed) && !empty($mixed)) {
      $mixed = func_get_args();
    }

    $this->groupby = $mixed;

    return $this;
  }

  /** @return static */
  public function having($mixed)
  {
    if (is_null($mixed)) {
      $this->having = null;
    } else {
      $this->getHaving()->mixed($mixed);
    }

    return $this;
  }

  /** @return Conditions */
  public function getHaving()
  {
    if (is_null($this->having)) {
      $this->having = new Conditions;
    }

    return $this->having;
  }

  protected function prepareColumns()
  {
    return !empty($this->columns) ? Query::QuoteColumns($this->columns) : '*';
  }

  protected function prepareJoin()
  {
    $str = '';
    if (!empty($this->join)) {
      foreach ($this->join as $arr) {
        $str .= (!empty($arr['type']) ? ' ' . strtoupper($arr['type']) : '')
          . ' JOIN ' . self::QuoteTables($arr['table'], $this->getTablePrefix()) . ' ON ' . $arr['on'];
      }
    }

    return $str;
  }

  protected function prepareGroupBy()
  {
    return !empty($this->groupby) ? ' GROUP BY ' . Query::QuoteColumns($this->groupby) : '';
  }

  protected function prepareHaving($as_sql = true)
  {
    $str = '';
    if ($cond = $this->getHaving()) {
      $str = $as_sql ? $cond->asSQL() : $cond->asStatement('having');
    }

    return !empty($str) ? ' HAVING ' . $str : '';
  }
}