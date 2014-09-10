QueryBuilder
============

Библиотека для построения SQL запросов SELECT, UPDATE, INSERT, DELETE.

Создание запросов начинается с создания объекта QueryBuilder. Можно сразу задать общий префикс для таблиц в БД:

        $qb = new \SQRT\QueryBuilder('awesome_');

После этого можно создавать объекты запросов, комбинируя в любой последовательности необходимые методы для настройки запроса:

**Важно:** вызовы `where()`, `having()` добавляют условия "в стек".
Чтобы стереть ранее добавленные условия нужно вызвать метод, явно указав NULL: `where(null)`

SELECT
------
        $q = $qb->select('pages p')
          ->join('news n', 'p.id = n.parent_id', 'LEFT')
          ->columns('p.id', 'p.name', 'COUNT(n.id) AS news')
          ->where(array('n.is_active' => 1, 'p.status IS NOT NULL', 'age > :age'))
          ->bind('age', 50)
          ->groupby('p.id')
          ->having(array('news' => 10, 'SUM(`age`) >= :age'))
          ->orderby('p.id', 'news')
          ->limit(10);

DELETE
------
        $q = $qb->delete('pages')
          ->where(array('is_active' => 1, 'p.status IS NOT NULL', 'age > :age'))
          ->bind('age', 50)
          ->orderby('id')
          ->limit(10);

UPDATE
------

        $q = $qb->update('pages')
          ->setEqual('one', 1)
          ->setExpr('`weight` = `one` + :add')
          ->bind('add', 'hundred tons')
          ->where(array('name' => 'John', 'visited_at <= CURDATE()'));

INSERT
------

        $q = $qb->insert('pages')
          ->setEqual('name', 'John')
          ->setExpr('age = age + :ten')
          ->bind('ten', 10);

После генерации запроса, его можно вывести в законченном виде SQL с подставленными значениями, или в виде подготовленного
выражения (prepared statements) с переменными (placeholders).

Все переменные, указанные на разных этапах формирования запроса, будут подготовлены для подстановки значений в PDO.

Используются только именованые переменные вида `:name` или `:id`. Безымянные вида `?` не поддерживаются.

        $q = $qb->update('pages')
          ->setEqual('one', 1)
          ->setExpr('`weight` = `one` + :add')
          ->bind('add', 'hundred tons')
          ->where(array('name' => 'John'));

        $q->asSQL();            // UPDATE `awesome_pages` SET `one`=1, `weight` = `one` + "hundred tons" WHERE `name`="John"
        $q->asStatement();      // UPDATE `awesome_pages` SET `one`=:set_one, `weight` = `one` + :add WHERE `name`=:where_name
        $q->getBindedValues();  // Array([:where_name] => John, [:add] => hundred tons, [:set_one] => 1)

Построение условий WHERE и HAVING
=================================

Для построения гибких условий WHERE и HAVING в запросах SELECT и UPDATE используется объекты `Conditions`, позволяющие в удобном виде создавать сложные многоуровневые условия.
Затем эти условия можно встраивать в запрос, или делать их вложенными друг в друга.

Можно создать объект с условиями и передать его в запрос:

    $c = new \SQRT\QueryBuilder\Conditions();

    $c->equal('one', 12)
      ->between('age', 10, 20)
      ->notLike('name', 'Peter%')
      ->in('status', array(1, 2, 3));

    $q->where($c);

Либо получить из запроса объект `Conditions` и работать с ним напрямую:

    $c = $q->getWhere()
      ->in('status', array(1, 2, 3))
      ->between('age', 10, 20);

**Важно:** если в одной части SQL-выражения требуется несколько раз указать условие для одного столбца, например id=1 OR id=2, возникнет конфликт именования переменных для подстановки.

Можно представить такую ситуацию:

        $q->getWhere()
          ->setJoinByOr();
          ->equal('one', 1)
          ->equal('one', 2)

Вызов `$q->asSQL()` будет корректно обработан, но при генерации prepared statement будет участвовать только одна переменная.

Следует либо изменить логику, например использовать `$q->getWhere()->in('one', array(1, 2))`, либо вручную формировать выражение и подставлять переменные в запрос:

        $q->getWhere()->expr('(one = :one OR one = :two)', array('one' => 1, 'two' => 2));

При этом в разных частях запроса обращение к одному столбцу допускается, т.к. имена переменных будут разные, например:

        $qb->update('pages')->setEqual('id', 12)->where(array('id' => 10)); // UPDATE `pages` SET `id`=:set_id WHERE `id`=:where_id

Больше примеров использования можно увидеть в тестах: /test/unit/...
--------------------------------------------------------------------