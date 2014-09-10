<?php

namespace SQRT\QueryBuilder\Query;

use SQRT\QueryBuilder\Query;
use SQRT\QueryBuilder\Conditions;

abstract class FilteredQuery extends Query
{
  /** @var Conditions */
  protected $where;
  protected $limit;
  protected $offset;
  protected $orderby;

  public function where($mixed)
  {
    if (is_null($mixed)) {
      $this->where = null;
    } else {
      $this->getWhere()->mixed($mixed);
    }

    return $this;
  }

  /** @return Conditions */
  public function getWhere()
  {
    if (is_null($this->where)) {
      $this->where = new Conditions;
    }

    return $this->where;
  }

  public function limit($limit, $offset = null)
  {
    $this->limit  = $limit;
    $this->offset = $offset;

    return $this;
  }

  public function page($page, $onpage)
  {
    $this->limit($onpage, (($page - 1) * $onpage));

    return $this;
  }

  public function orderby($mixed, $_ = null)
  {
    if (!is_array($mixed)) {
      $mixed = func_get_args();
    }

    $this->orderby = $mixed;

    return $this;
  }

  protected function prepareWhere($as_sql = true)
  {
    $str = '';
    if ($cond = $this->getWhere()) {
      $str = $as_sql ? $cond->asSQL() : $cond->asStatement('where');
    }

    return !empty($str) ? ' WHERE ' . $str : '';
  }

  protected function prepareOrderBy()
  {
    return !empty($this->orderby) ? ' ORDER BY ' . Query::QuoteColumns($this->orderby) : '';
  }

  protected function prepareLimit()
  {
    $str = '';
    if ($this->limit) {
      $str = ' LIMIT ' . ($this->offset ? $this->offset . ', ' : '') . $this->limit;
    }

    return $str;
  }

  protected function processWhereBindedVars($vars)
  {
    if ($this->where) {
      if ($arr = $this->getWhere()->getBindedValues('where')) {
        $vars = $vars ? array_merge($arr, $vars) : $arr;
      }
    }

    return $vars;
  }
}