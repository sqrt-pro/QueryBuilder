<?php

require_once __DIR__ . '/../init.php';

use SQRT\QueryBuilder\Conditions;
use SQRT\QueryBuilder\Condition\Equal;
use SQRT\QueryBuilder\Condition\In;
use SQRT\QueryBuilder\Condition\Between;
use SQRT\QueryBuilder\Condition\Like;
use SQRT\QueryBuilder\Condition\Greater;
use SQRT\QueryBuilder\Condition\Less;
use SQRT\QueryBuilder\Condition\Expr;

class conditionsTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider dataEqual
   */
  function testEqual($column, $value, $exp_sql, $exp_stmt, $vals, $prefix = null, $not_equal = null)
  {
    $c = new Equal($column, $value, $not_equal);

    $this->assertEquals($exp_sql, $c->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_stmt, $c->asStatement($prefix), 'В виде выражения');
    $this->assertEquals($vals, $c->getBindedValues($prefix), 'Подставленные значения');
  }

  function dataEqual()
  {
    return array(
      array('one', 1, '`one`=1', '`one`=:one', array(':one' => 1)),
      array('one', 1, '`one`=1', '`one`=:where_one', array(':where_one' => 1), 'where'),
      array('one', 'two', '`one`="two"', '`one`=:where_one', array(':where_one' => 'two'), 'where'),
      array('one', null, '`one` IS NULL', '`one` IS NULL', false, 'where'),
      array('one', null, '`one` IS NOT NULL', '`one` IS NOT NULL', false, 'where', true),
      array('one', 1, '`one`!=1', '`one`!=:one', array(':one' => 1), null, true),
    );
  }

  function testIn()
  {
    $c = new In('one', array(1, 'two', null));

    $this->assertEquals('`one` IN (1, "two", NULL)', $c->asSQL(), 'В виде SQL');
    $this->assertEquals(
      '`one` IN (:where_one_1, :where_one_2, :where_one_3)',
      $c->asStatement('where'),
      'В виде выражения'
    );

    $vals = array(
      ':where_one_1' => 1,
      ':where_one_2' => 'two',
      ':where_one_3' => null,
    );
    $this->assertEquals($vals, $c->getBindedValues('where'), 'Подставленные значения');
  }

  /**
   * @dataProvider dataBetween
   */
  function testBetween($column, $from, $to, $exp_sql, $exp_stmt, $vals, $prefix = null, $not_between = null, $date_format = null) {
    $c = new Between($column, $from, $to, $not_between);

    if ($date_format) {
      $c->setDateFormat($date_format);
    }

    $this->assertEquals($exp_sql, $c->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_stmt, $c->asStatement($prefix), 'В виде выражения');
    $this->assertEquals($vals, $c->getBindedValues($prefix), 'Подставленные значения');
  }

  function dataBetween()
  {
    $plus_day = date('Y-m-d H:i', strtotime('+1 day'));

    return array(
      array(
        'age',
        10,
        'twenty',
        '`age` BETWEEN 10 AND "twenty"',
        '`age` BETWEEN :age_from AND :age_to',
        array(':age_from' => 10, ':age_to' => 'twenty')
      ),
      array(
        'birthday',
        '01.01.1986',
        '+1 day',
        '`birthday` BETWEEN "1986-01-01 00:00" AND "'.$plus_day.'"',
        '`birthday` BETWEEN :birthday_from AND :birthday_to',
        array(':birthday_from' => '1986-01-01 00:00', ':birthday_to' => $plus_day),
        null,
        false,
        'Y-m-d H:i'
      ),
      array(
        'u.age',
        10,
        12,
        'u.age NOT BETWEEN 10 AND 12',
        'u.age NOT BETWEEN :where_u_age_from AND :where_u_age_to',
        array(':where_u_age_from' => 10, ':where_u_age_to' => 12),
        'where',
        true
      )
    );
  }

  function testLike()
  {
    $c = new Like('one', 'строка%поиска');

    $this->assertEquals('`one` LIKE "строка%поиска"', $c->asSQL(), 'В виде SQL');
    $this->assertEquals('`one` LIKE :where_one', $c->asStatement('where'), 'В виде выражения');
    $this->assertEquals(array(':where_one' => 'строка%поиска'), $c->getBindedValues('where'), 'Подставленные значения');

    $c->setNot(true);

    $this->assertEquals('`one` NOT LIKE "строка%поиска"', $c->asSQL(), 'NOT LIKE В виде SQL');
    $this->assertEquals('`one` NOT LIKE :where_one', $c->asStatement('where'), 'NOT LIKE В виде выражения');
  }

  function testGreater()
  {
    $c = new Greater('one', 10);

    $this->assertEquals('`one`>10', $c->asSQL(), 'В виде SQL');
    $this->assertEquals('`one`>:where_one_gt', $c->asStatement('where'), 'В виде выражения');
    $this->assertEquals(array(':where_one_gt' => 10), $c->getBindedValues('where'), 'Подставленные значения');

    $c->setOrEqual(true);

    $this->assertEquals('`one`>=10', $c->asSQL(), 'Or EQ В виде SQL');
    $this->assertEquals('`one`>=:where_one_gt', $c->asStatement('where'), 'Or EQ В виде выражения');
  }

  function testLess()
  {
    $c = new Less('one', 10);

    $this->assertEquals('`one`<10', $c->asSQL(), 'В виде SQL');
    $this->assertEquals('`one`<:where_one_lt', $c->asStatement('where'), 'В виде выражения');
    $this->assertEquals(array(':where_one_lt' => 10), $c->getBindedValues('where'), 'Подставленные значения');

    $c->setOrEqual(true);

    $this->assertEquals('`one`<=10', $c->asSQL(), 'Or EQ В виде SQL');
    $this->assertEquals('`one`<=:where_one_lt', $c->asStatement('where'), 'Or EQ В виде выражения');
  }

  /**
   * @dataProvider dataExpr
   */
  function testExpr($expr, $values, $exp_sql, $exp_vals)
  {
    $c = new Expr($expr, $values);

    $this->assertEquals($exp_sql, $c->asSQL(), 'В виде SQL');
    $this->assertEquals($exp_vals, $c->getBindedValues(), 'Подставленные значения');
  }

  function dataExpr()
  {
    return array(
      array(
        '`ip` = INET_ATON(:ip)',
        array('ip' => '127.0.0.1'),
        '`ip` = INET_ATON("127.0.0.1")',
        array(':ip' => '127.0.0.1')
      ),
      array(
        '`name` LIKE :srch OR `surname` LIKE :srch',
        array('srch' => 'wow%yeah'),
        '`name` LIKE "wow%yeah" OR `surname` LIKE "wow%yeah"',
        array(':srch' => 'wow%yeah')
      ),
    );
  }

  function testConditions()
  {
    $c1 = new Conditions();

    $c1->equal('one', 12)
      ->between('age', 10, 20)
      ->notLike('name', 'Peter%')
      ->in('status', array(1, 2, 3));

    $exp_sql = '`one`=12 AND `age` BETWEEN 10 AND 20 AND `name` NOT LIKE "Peter%" AND `status` IN (1, 2, 3)';
    $exp_stmt = '`one`=:one AND `age` BETWEEN :age_from AND :age_to AND `name` NOT LIKE :name AND `status` IN (:status_1, :status_2, :status_3)';
    $this->assertEquals($exp_sql, $c1->asSQL(), 'В виде условия AND');
    $this->assertEquals($exp_stmt, $c1->asStatement(), 'В виде выражения AND');

    $exp_sql = '(`one`=12 OR `age` BETWEEN 10 AND 20 OR `name` NOT LIKE "Peter%" OR `status` IN (1, 2, 3))';
    $exp_stmt = '(`one`=:wow_one OR `age` BETWEEN :wow_age_from AND :wow_age_to OR `name` NOT LIKE :wow_name OR `status` IN (:wow_status_1, :wow_status_2, :wow_status_3))';
    $c1->setJoinByOr();
    $this->assertEquals($exp_sql, $c1->asSQL(), 'В виде условия OR');
    $this->assertEquals($exp_stmt, $c1->asStatement('wow'), 'В виде выражения OR и префиксом');

    $c1->setNot();

    $exp_sql = '`abc`="cde" AND NOT (`one`=12 OR `age` BETWEEN 10 AND 20 OR `name` NOT LIKE "Peter%" OR `status` IN (1, 2, 3))';
    $exp_stmt = '`abc`=:wow_abc AND NOT (`one`=:wow_one OR `age` BETWEEN :wow_age_from AND :wow_age_to OR `name` NOT LIKE :wow_name OR `status` IN (:wow_status_1, :wow_status_2, :wow_status_3))';

    $c2 = new Conditions();
    $c2->equal('abc', 'cde')
       ->add($c1);

    $this->assertEquals($exp_sql, $c2->asSQL(), 'Условие встроено в другое с NOT');
    $this->assertEquals($exp_stmt, $c2->asStatement('wow'), 'Условие встроено в другое с NOT и общим префиксом');

    $arr = array(
      ':wow_status_1' => 1,
      ':wow_status_2' => 2,
      ':wow_status_3' => 3,
      ':wow_name'     => 'Peter%',
      ':wow_age_from' => 10,
      ':wow_age_to'   => 20,
      ':wow_one'      => 12,
      ':wow_abc'      => 'cde'
    );
    $this->assertEquals($arr, $c2->getBindedValues('wow'), 'Подставленные значения');
  }
  
  function testMergeConditions()
  {
    $c1 = new Conditions();

    $c1->equal('one', 12)
      ->between('age', 10, 20);
    
    $c2 = new Conditions();
    $c1->add($c2);

    $this->assertEquals('`one`=12 AND `age` BETWEEN 10 AND 20', $c1->asSQL(), 'Мердж c пустыми условиями');

    $c2->like('name', 'ололо')
      ->equal('id', 123);

    $c1->add($c2);

    $exp = '`one`=12 AND `age` BETWEEN 10 AND 20 AND `name` LIKE "ололо" AND `id`=123';
    $this->assertEquals($exp, $c1->asSQL(), 'Мердж двух условий');

    $c3 = new Conditions();
    $c3->add($c2);
    $this->assertEquals('`name` LIKE "ололо" AND `id`=123', $c3->asSQL(), 'Добавление к пустым условиям');

    $c1->setJoinByOr();

    $exp = '(`one`=12 OR `age` BETWEEN 10 AND 20 OR (`name` LIKE "ололо" AND `id`=123))';
    $this->assertEquals($exp, $c1->asSQL(), 'AND вложенный в OR');
  }
}