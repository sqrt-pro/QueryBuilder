<?php

require_once __DIR__ . '/../init.php';

class quotesTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider dataQuoteTable
   */
  function testQuoteTable($table, $exp, $prefix = null)
  {
    $this->assertEquals($exp, \SQRT\QueryBuilder\Query::QuoteTables($table, $prefix));
  }

  function dataQuoteTable()
  {
    return array(
      array('pages', '`pages`'),
      array('pages', '`some_pages`', 'some_'),
      array('pages p', '`pages` `p`'),
      array('pages p', '`some_pages` `p`', 'some_'),
      array(array('pages p', 'news n'), '`pages` `p`, `news` `n`'),
      array(array('pages p', 'news n'), '`some_pages` `p`, `some_news` `n`', 'some_'),
    );
  }
}