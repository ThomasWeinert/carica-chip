<?php

namespace Carica\Chip {

  include_once(__DIR__.'/Mocks/ConsecutiveParameters.php');

  abstract class TestCase extends \PHPUnit_Framework_TestCase {

    public function withConsecutive() {
      return new Tests\Mocks\ConsecutiveParameters(func_get_args());
    }
  }
}