<?php

namespace SQRT\QueryBuilder\Condition;

use SQRT\QueryBuilder\Condition;
use SQRT\QueryBuilder\Query;

class Between extends Condition
{
  protected $date_format;

  function __construct($column, $from, $to, $not = null)
  {
    $this->column = $column;
    $this->values = array('from' => $from, 'to' => $to);
    $this->not    = $not;
  }

  public function asSQL()
  {
    return sprintf(
      '%s %sBETWEEN %s AND %s',
      Query::QuoteKey($this->column),
      ($this->not ? 'NOT ' : ''),
      Query::QuoteValue($this->prepareValue($this->values['from'])),
      Query::QuoteValue($this->prepareValue($this->values['to']))
    );
  }

  public function asStatement($prefix = null)
  {
    return sprintf(
      '%s %sBETWEEN %s AND %s',
      Query::QuoteKey($this->column),
      ($this->not ? 'NOT ' : ''),
      Query::Placeholder($this->column . '_from', $prefix),
      Query::Placeholder($this->column . '_to', $prefix)
    );
  }

  public function getBindedValues($prefix = null)
  {
    $arr = array();
    foreach ($this->values as $key => $val) {
      $arr[Query::Placeholder($this->column . '_' . $key, $prefix)] = $this->prepareValue($val);
    }

    return $arr;
  }

  /** Формат даты - если задан, значения автоматически преобразуются в него */
  public function setDateFormat($date_format)
  {
    $this->date_format = $date_format;

    return $this;
  }

  /** Формат даты - если задан, значения автоматически преобразуются в него */
  public function getDateFormat()
  {
    return $this->date_format;
  }

  /** Подготовка значения */
  protected function prepareValue($val)
  {
    if ($this->date_format) {
      if ($t = strtotime($val)) {
        return date($this->date_format, $t);
      }

      return false;
    }

    return $val;
  }
}