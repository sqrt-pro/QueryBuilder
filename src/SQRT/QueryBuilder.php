<?php

namespace SQRT;

use SQRT\QueryBuilder\Query\Delete;
use SQRT\QueryBuilder\Query\Insert;
use SQRT\QueryBuilder\Query\Select;
use SQRT\QueryBuilder\Query\Update;

class QueryBuilder
{
  protected $prefix;

  function __construct($prefix = null)
  {
    $this->setPrefix($prefix);
  }

  /** @return Select */
  public function select($table)
  {
    return new Select($table, $this->prefix);
  }

  /** @return Update */
  public function update($table)
  {
    return new Update($table, $this->prefix);
  }

  /** @return Insert */
  public function insert($table)
  {
    return new Insert($table, $this->prefix);
  }

  /** @return Delete */
  public function delete($table)
  {
    return new Delete($table, $this->prefix);
  }

  /** Префикс для таблиц в запросах */
  public function setPrefix($prefix)
  {
    $this->prefix = $prefix;

    return $this;
  }

  /** Префикс для таблиц в запросах */
  public function getPrefix()
  {
    return $this->prefix;
  }
}