<?php

class SelectTest extends PHPUnit_Framework_TestCase
{
  function testTable()
  {
    $qb = new \SQRT\QueryBuilder();

    $q = $qb->select('pages');
    $exp = 'SELECT * FROM `pages`';
    $this->assertEquals($exp, $q->asSQL(), 'Полная выборка из одной таблицы');

    $q = $qb->select(array('pages', 'news'));
    $exp = 'SELECT * FROM `pages`, `news`';
    $this->assertEquals($exp, $q->asSQL(), 'Полная выборка из нескольких таблиц');

    $q = $qb->select(array('pages p', 'news n'))->setTablePrefix('some_');
    $exp = 'SELECT * FROM `some_pages` `p`, `some_news` `n`';
    $this->assertEquals($exp, $q->asSQL(), 'Полная выборка из нескольких таблиц с префиксами');
  }

  function testColumns()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $exp = 'SELECT * FROM `pages`';
    $this->assertEquals($exp, $q->asSQL(), 'Столбцы не указаны');

    $exp = 'SELECT `id`, `name`, `age` FROM `pages`';
    $q->columns('id', 'name', 'age');
    $this->assertEquals($exp, $q->asSQL(), 'Столбцы в виде аргументов');

    $exp = 'SELECT id, name, age FROM `pages`';
    $q->columns('id, name, age');
    $this->assertEquals($exp, $q->asSQL(), 'Столбцы в виде строки без экранирования');

    $exp = 'SELECT `id`, `name`, `age` FROM `pages`';
    $q->columns(array('id', 'name', 'age'));
    $this->assertEquals($exp, $q->asSQL(), 'Столбцы в виде массива');

    $exp = 'SELECT p.id, COUNT(*), `some` as `awesome` FROM `pages`';
    $q->columns('p.id', 'COUNT(*)', '`some` as `awesome`');
    $this->assertEquals($exp, $q->asSQL(), 'Столбцы содержащие спецсимволы не экранируются');
  }

  function testOrderBy()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $exp = 'SELECT * FROM `pages` ORDER BY `age`, created_at DESC';

    $q->orderby('age', 'created_at DESC');
    $this->assertEquals($exp, $q->asSQL(), 'Сортировка указана в аргументах функции');

    $q->orderby(array('age', 'created_at DESC'));
    $this->assertEquals($exp, $q->asSQL(), 'Сортировка указана как массив');

    $q->orderby('`age`, created_at DESC');
    $this->assertEquals($exp, $q->asSQL(), 'Сортировка указана как строка');

    $q->orderby(null);
    $this->assertEquals('SELECT * FROM `pages`', $q->asSQL(), 'Сортировка удалена');
  }

  function testGroupBy()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $exp = 'SELECT * FROM `pages` GROUP BY `one`, `two`';

    $q->groupby('one', 'two');
    $this->assertEquals($exp, $q->asSQL(), 'Группировка указана в аргументах функции');

    $q->groupby(array('one', 'two'));
    $this->assertEquals($exp, $q->asSQL(), 'Группировка указана как массив');

    $q->groupby('`one`, `two`');
    $this->assertEquals($exp, $q->asSQL(), 'Группировка указана как строка');

    $q->groupby(null);
    $this->assertEquals('SELECT * FROM `pages`', $q->asSQL(), 'Условие удалено');
  }

  function testWhere()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $q->where(42);
    $q->where(array('status' => 'new', 'age' => array(12, 13, 14)));

    $exp_sql = 'SELECT * FROM `pages` WHERE `id`=42 AND `status`="new" AND `age` IN (12, 13, 14)';
    $exp_stmt = 'SELECT * FROM `pages` WHERE `id`=:where_id AND `status`=:where_status AND `age` IN (:where_age_1, :where_age_2, :where_age_3)';
    $this->assertEquals($exp_sql, $q->asSQL(), 'Запрос в виде SQL');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'Запрос в виде выражения');

    $q->where(null);
    $exp = 'SELECT * FROM `pages`';
    $this->assertEquals($exp, $q->asSQL(), 'Условие удалено');
  }

  function testHaving()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $q->having(array('total' => 10));

    $exp_sql = 'SELECT * FROM `pages` HAVING `total`=10';
    $exp_stmt = 'SELECT * FROM `pages` HAVING `total`=:having_total';
    $this->assertEquals($exp_sql, $q->asSQL(), 'Запрос в виде SQL');
    $this->assertEquals($exp_stmt, $q->asStatement(), 'Запрос в виде выражения');
    $this->assertEquals(array(':having_total' => 10), $q->getBindedValues(), 'Подставляемые значения');

    $q->having(null);
    $exp = 'SELECT * FROM `pages`';
    $this->assertEquals($exp, $q->asSQL(), 'Условие удалено');
  }

  function testLimit()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $q->limit(10);

    $exp = 'SELECT * FROM `pages` LIMIT 10';
    $this->assertEquals($exp, $q->asSQL(), 'Лимит без отступа');

    $q->limit(10, 20);

    $exp = 'SELECT * FROM `pages` LIMIT 20, 10';
    $this->assertEquals($exp, $q->asSQL(), 'Лимит с отступом');

    $q->page(3, 20);
    $exp = 'SELECT * FROM `pages` LIMIT 40, 20';
    $this->assertEquals($exp, $q->asSQL(), 'Указание отступа в виде страниц');

    $q->page(null, 20);
    $exp = 'SELECT * FROM `pages` LIMIT 20';
    $this->assertEquals($exp, $q->asSQL(), 'Указание лимита в виде страниц');
  }

  function testJoin()
  {
    $qb = new \SQRT\QueryBuilder();

    $q = $qb->select('pages p')->join('users u', 'u.id = p.user_id');

    $exp = 'SELECT * FROM `pages` `p` JOIN `users` `u` ON u.id = p.user_id';
    $this->assertEquals($exp, $q->asSQL(), 'Простой JOIN на одну таблицу');

    $q->setTablePrefix('some_')->join('page_types pt', 'pt.id = p.type_id', 'left outer');

    $exp = 'SELECT * FROM `some_pages` `p` JOIN `some_users` `u` ON u.id = p.user_id LEFT OUTER JOIN `some_page_types` `pt` ON pt.id = p.type_id';
    $this->assertEquals($exp, $q->asSQL(), 'JOIN на несколько таблиц с префиксом');
  }

  function testAll()
  {
    $qb = new \SQRT\QueryBuilder();
    $q  = $qb->select('pages p')
      ->join('news n', 'p.id = n.parent_id', 'LEFT')
      ->columns('p.id', 'p.name', 'COUNT(n.id) AS news')
      ->where(array('n.is_active' => 1, 'p.status IS NOT NULL', 'age > :age'))
      ->bind('age', 50)
      ->groupby('p.id')
      ->having(array('news' => 10, 'SUM(`age`) >= :age'))
      ->orderby('p.id', 'news')
      ->limit(10);

    $exp = 'SELECT p.id, p.name, COUNT(n.id) AS news FROM `pages` `p` LEFT JOIN `news` `n` ON p.id = n.parent_id '
      . 'WHERE n.is_active=1 AND p.status IS NOT NULL AND age > 50 '
      . 'GROUP BY p.id HAVING `news`=10 AND SUM(`age`) >= 50 ORDER BY p.id, `news` LIMIT 10';

    $vars = array(
      ':where_n_is_active' => 1,
      ':having_news'       => 10,
      ':age'               => 50
    );

    $this->assertEquals($exp, $q->asSQL(), 'Полный запрос');
    $this->assertEquals($vars, $q->getBindedValues(), 'Переменные для подстановки');
  }

  function testToString()
  {
    $qb = new \SQRT\QueryBuilder();
    $q = $qb->select('pages');

    $this->assertEquals((string)$q, $q->asSQL(), 'Приведение к строке');
  }
}