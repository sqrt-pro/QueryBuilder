<?php

namespace SQRT\QueryBuilder;

use SQRT\QueryBuilder\Condition\Equal;
use SQRT\QueryBuilder\Condition\In;
use SQRT\QueryBuilder\Condition\Between;
use SQRT\QueryBuilder\Condition\Like;
use SQRT\QueryBuilder\Condition\Greater;
use SQRT\QueryBuilder\Condition\Less;
use SQRT\QueryBuilder\Condition\Expr;

class Conditions extends Condition
{
  /** @var Condition[] */
  protected $conditions;
  protected $join_by_and = true;

  /** Условие в виде запроса SQL */
  public function asSQL()
  {
    $arr = array();
    if ($this->conditions) {
      foreach ($this->conditions as $cond) {
        $arr[] = $cond->asSQL();
      }
    }

    return $this->processPieces($arr);
  }

  /** Условие в виде подготовленного выражения с переменными */
  public function asStatement($prefix = null)
  {
    $arr = array();
    if ($this->conditions) {
      foreach ($this->conditions as $cond) {
        $arr[] = $cond->asStatement($prefix);
      }
    }

    return $this->processPieces($arr);
  }

  /** Значения переменных для подстановки в запрос */
  public function getBindedValues($prefix = null)
  {
    $arr = array();
    if ($this->conditions) {
      foreach ($this->conditions as $cond) {
        $vals = $cond->getBindedValues($prefix);
        if (is_array($vals)) {
          $arr = array_merge($vals, $arr);
        }
      }
    }

    return $arr;
  }

  /** Произвольный формат */
  public function mixed($mixed)
  {
    if ($mixed instanceof Condition) {
      $this->condition($mixed);
    } elseif (is_array($mixed)) {
      foreach ($mixed as $key => $val) {
        if (is_array($val)) {
          $this->in($key, $val);
        } elseif (is_numeric($key)) {
          $this->expr($val);
        } else {
          $this->equal($key, $val);
        }
      }
    } elseif (is_numeric($mixed)) {
      $this->equal('id', $mixed);
    } else {
      $this->expr($mixed);
    }

    return $this;
  }

  /** Встраивание условия или набора условий */
  public function condition(Condition $cond)
  {
    $this->conditions[] = $cond;

    return $this;
  }

  public function expr($expr, $values = null)
  {
    $this->conditions[] = new Expr($expr, $values);

    return $this;
  }

  public function equal($column, $value)
  {
    $this->conditions[] = new Equal($column, $value);

    return $this;
  }

  public function notEqual($column, $value)
  {
    $this->conditions[] = new Equal($column, $value, true);

    return $this;
  }

  public function in($column, $array)
  {
    $this->conditions[] = new In($column, $array);

    return $this;
  }

  public function notIn($column, $array)
  {
    $this->conditions[] = new In($column, $array, true);

    return $this;
  }

  public function between($column, $from, $to, $date_format = null)
  {
    $btw = new Between($column, $from, $to);
    if ($date_format) {
      $btw->setDateFormat($date_format);
    }

    $this->conditions[] = $btw;

    return $this;
  }

  public function notBetween($column, $from, $to, $date_format = null)
  {
    $btw = new Between($column, $from, $to, true);
    if ($date_format) {
      $btw->setDateFormat($date_format);
    }

    $this->conditions[] = $btw;

    return $this;
  }

  public function like($column, $expr)
  {
    $this->conditions[] = new Like($column, $expr);

    return $this;
  }

  public function notLike($column, $expr)
  {
    $this->conditions[] = new Like($column, $expr, true);

    return $this;
  }

  public function greater($column, $value)
  {
    $this->conditions[] = new Greater($column, $value);

    return $this;
  }

  public function greaterOrEqual($column, $value)
  {
    $this->conditions[] = new Greater($column, $value, true);

    return $this;
  }

  public function less($column, $value)
  {
    $this->conditions[] = new Less($column, $value);

    return $this;
  }

  public function lessOrEqual($column, $value)
  {
    $this->conditions[] = new Less($column, $value, true);

    return $this;
  }

  public function setJoinByAnd()
  {
    $this->join_by_and = true;

    return $this;
  }

  public function setJoinByOr()
  {
    $this->join_by_and = false;

    return $this;
  }

  public function getConditions()
  {
    return $this->conditions;
  }

  /** Собираем общую строку из отдельных условий */
  protected function processPieces($arr)
  {
    if (empty($arr)) {
      return '';
    }

    $glue   = ' AND ';
    $braces = (bool)$this->not;
    if (!$this->join_by_and) {
      $glue   = ' OR ';
      $braces = true;
    }

    $str = join($glue, $arr);

    return ($this->not ? 'NOT ' : '') . ($braces ? '(' . $str . ')' : $str);
  }
}