<?php

require_once __DIR__ . '/../init.php';

class deleteTest extends PHPUnit_Framework_TestCase
{
  function testAll()
  {
    $qb = new \SQRT\QueryBuilder();

    $q = $qb
      ->setPrefix('some_')
      ->delete('pages p')
      ->where(array('n.is_active' => 1, 'p.status IS NOT NULL', 'age > :age'))
      ->bind('age', 50)
      ->orderby('p.id', 'news')
      ->limit(10);

    $exp_sql  = 'DELETE FROM `some_pages` `p` WHERE n.is_active=1 AND p.status IS NOT NULL AND age > 50 ORDER BY p.id, `news` LIMIT 10';
    $exp_stmt = 'DELETE FROM `some_pages` `p` WHERE n.is_active=:where_n_is_active AND p.status IS NOT NULL AND age > :age ORDER BY p.id, `news` LIMIT 10';
    $vars = array(
      ':where_n_is_active' => 1,
      ':age' => 50
    );

    $this->assertEquals($exp_sql, $q->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'В виде выражения');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки');
  }
}