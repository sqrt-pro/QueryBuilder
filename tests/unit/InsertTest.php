<?php

class InsertTest extends PHPUnit_Framework_TestCase
{
  function testAll()
  {
    $qb = new \SQRT\QueryBuilder();

    $q = $qb
      ->setPrefix('some_')
      ->insert('pages p')
      ->setEqual('name', 'John')
      ->setExpr('age = age + :ten')
      ->bind('ten', 10);

    $exp_sql  = 'INSERT INTO `some_pages` `p` SET `name`="John", age = age + 10';
    $exp_stmt = 'INSERT INTO `some_pages` `p` SET `name`=:set_name, age = age + :ten';
    $vars     = array(
      ':ten'      => 10,
      ':set_name' => 'John'
    );

    $this->assertEquals($exp_sql, $q->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'В виде выражения');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки');

    $q->setOnDuplicateKeyUpdate(true);

    $exp_sql  = 'INSERT INTO `some_pages` `p` SET `name`="John", age = age + 10 ON DUPLICATE KEY UPDATE `name`="John", age = age + 10';
    $exp_stmt = 'INSERT INTO `some_pages` `p` SET `name`=:set_name, age = age + :ten ON DUPLICATE KEY UPDATE `name`=:set_name, age = age + :ten';

    $this->assertEquals($exp_sql, $q->asSQL(), 'В виде SQL on duplicate key update');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'В виде выражения on duplicate key update');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки on duplicate key update');
  }
}