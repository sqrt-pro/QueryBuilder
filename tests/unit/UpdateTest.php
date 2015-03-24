<?php

class UpdateTest extends PHPUnit_Framework_TestCase
{
  function testSet()
  {
    $qb = new \SQRT\QueryBuilder();

    $q = $qb->update('pages')
      ->setEqual('one', 1)
      ->setEqual('two', 'two')
      ->setEqual('four', null)
      ->setExpr('`weight` = `one` + :add')
      ->bind('add', 'hundred tons');

    $exp_sql  = 'UPDATE `pages` SET `one`=1, `two`="two", `four`=NULL, `weight` = `one` + "hundred tons"';
    $exp_stmt = 'UPDATE `pages` SET `one`=:set_one, `two`=:set_two, `four`=NULL, `weight` = `one` + :add';
    $vars     = array(
      ':add'     => 'hundred tons',
      ':set_one' => 1,
      ':set_two' => 'two'
    );

    $this->assertEquals($exp_sql, $q->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'В виде выражения');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки');

    $q = $qb->update('pages')
      ->setFromArray(
        array(
          'one'  => 1,
          'two'  => 'two',
          'four' => null,
          '`weight` = `one` + :add'
        )
      )
      ->bind('add', 'hundred tons');

    $this->assertEquals($exp_sql, $q->asSQL(), 'В виде SQL из массива');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'В виде выражения из массива');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки из массива');
  }

  function testAll()
  {
    $qb = new \SQRT\QueryBuilder();

    $q = $qb
      ->setPrefix('some_')
      ->update('pages p')
      ->where(42)
      ->setEqual('name', 'John')
      ->setExpr('age = age + :ten')
      ->bind('ten', 10)
      ->orderby('id DESC')
      ->limit(1);

    $exp_sql  = 'UPDATE `some_pages` `p` SET `name`="John", age = age + 10 WHERE `id`=42 ORDER BY id DESC LIMIT 1';
    $exp_stmt = 'UPDATE `some_pages` `p` SET `name`=:set_name, age = age + :ten WHERE `id`=:where_id ORDER BY id DESC LIMIT 1';
    $vars     = array(
      ':where_id' => 42,
      ':ten'      => 10,
      ':set_name' => 'John'
    );

    $this->assertEquals($exp_sql, $q->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'В виде выражения');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки');
  }
}