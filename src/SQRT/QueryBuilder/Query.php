<?php

namespace SQRT\QueryBuilder;

abstract class Query
{
  protected $table_prefix;
  protected $table;
  protected $values;
  protected $set_arr;

  function __construct($table, $table_prefix = null)
  {
    $this->table        = $table;
    $this->table_prefix = $table_prefix;
  }

  function __toString()
  {
    return $this->asSQL();
  }

  /** Создать SQL */
  abstract public function asSQL();

  /** Создать выражение с подстановкой переменных */
  abstract public function asStatement();

  /** Список переменных для подстановки */
  abstract public function getBindedValues();

  public function bind($key, $value)
  {
    $this->values[Query::Placeholder($key)] = $value;

    return $this;
  }

  public function bindArray(array $array)
  {
    foreach ($array as $key => $val) {
      $this->bind($key, $val);
    }

    return $this;
  }

  public function setTablePrefix($prefix)
  {
    $this->table_prefix = $prefix;

    return $this;
  }

  public function getTablePrefix()
  {
    return $this->table_prefix;
  }

  protected function setEqual($column, $value)
  {
    if (is_null($value)) {
      $this->setExpr(Query::QuoteKey($column) . '=NULL');
    } else {
      $this->set_arr[$column] = $value;
    }

    return $this;
  }

  protected function setExpr($expr)
  {
    $this->set_arr[] = $expr;

    return $this;
  }

  protected function setFromArray(array $array)
  {
    foreach ($array as $key => $val) {
      if (is_numeric($key)) {
        $this->setExpr($val);
      } else {
        $this->setEqual($key, $val);
      }
    }

    return $this;
  }

  protected function prepareTable()
  {
    return Query::QuoteTables($this->table, $this->getTablePrefix());
  }

  protected function prepareSet($as_sql = true)
  {
    $str = $this->prepareSetValues($as_sql);

    return !empty($str) ? ' SET ' . $str : '';
  }

  protected function prepareSetValues($as_sql = true)
  {
    $str = '';
    if (!empty($this->set_arr)) {
      $arr = array();
      foreach ($this->set_arr as $key => $val) {
        if (is_numeric($key)) {
          $arr[] = $val;
        } else {
          $arr[] = Query::QuoteKey($key) . '=' . ($as_sql ? Query::QuoteValue($val) : Query::Placeholder($key, 'set'));
        }
      }
      $str = join(', ', $arr);
    }

    return $str;
  }

  protected function processBindedVarsToSQL($str)
  {
    if ($this->values) {
      foreach ($this->values as $key => $val) {
        $str = str_replace($key, Query::QuoteValue($val), $str);
      }
    }

    return $str;
  }

  protected function processSetBindedVars($vars)
  {
    if ($this->set_arr) {
      foreach ($this->set_arr as $key => $val) {
        if (!is_numeric($key)) {
          $vars[Query::Placeholder($key, 'set')] = $val;
        }
      }
    }

    return $vars;
  }

  public static function QuoteColumns($mixed)
  {
    if (is_array($mixed)) {
      $arr = array();
      foreach ($mixed as $val) {
        $arr[] = static::QuoteColumns($val);
      }
      $mixed = join(', ', $arr);
    } else {
      $mixed = static::QuoteKey($mixed);
    }

    return $mixed;
  }

  public static function QuoteTables($mixed, $prefix = null)
  {
    if (is_array($mixed)) {
      $arr = array();
      foreach ($mixed as $val) {
        $arr[] = static::QuoteTables($val, $prefix);
      }
      $mixed = join(', ', $arr);
    } else {
      if (strpos($mixed, '`') === false && strpos($mixed, ',') === false) {
        $arr = explode(' ', $mixed, 2);
        if (count($arr) == 2) {
          $mixed = static::QuoteKey($prefix . $arr[0]) . ' ' . static::QuoteKey($arr[1]);
        } else {
          $mixed = static::QuoteKey($prefix . $mixed);
        }
      }
    }

    return $mixed;
  }

  public static function QuoteKey($key)
  {
    return static::HasSpecChar($key) ? $key : '`' . $key . '`';
  }

  public static function QuoteValue($value)
  {
    if (is_null($value)) {
      return 'NULL';
    }

    if (!ini_get('magic_quotes_gpc')) {
      $value = addslashes($value);
    }

    return is_numeric($value) ? $value : '"' . $value . '"';
  }

  public static function Placeholder($key, $prefix = null)
  {
    return ':' . static::CleanKeyName(($prefix ? $prefix . '_' : '') . $key);
  }

  public static function CleanKeyName($key)
  {
    return preg_replace('/[^a-z0-9]/i', '_', $key);
  }

  protected static function HasSpecChar($str)
  {
    return preg_match('/[><,\.()`\s\*=:]+/is', $str);
  }
}