<?php

require_once __DIR__ . '/init.php';

class AllUnitTest extends PHPUnit_Framework_TestSuite
{
  public static function suite()
  {
    return self::processDir(__DIR__ . '/unit/*');
  }

  protected function processDir($dir, $suite = null)
  {
    if (is_null($suite)) {
      $suite = new PHPUnit_Framework_TestSuite('AllUnit');
    }

    $arr = glob($dir);
    foreach ($arr as $str) {
      if (is_dir($str)) {
        self::processDir($str . '/*', $suite);
      } else {
        $suite->addTestFile($str);
      }
    }

    return $suite;
  }
}