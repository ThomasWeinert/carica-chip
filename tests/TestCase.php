<?php

namespace Carica\Chip {

  use Phake;

  abstract class TestCase extends \PHPUnit_Framework_TestCase {

    /**
     * @return \Phake_IMock|\Carica\Io\Device\Pin
     */
    protected function getMockForPin() {
      $pin = Phake::mock('\\Carica\\Io\\Device\\Pin');
      return $pin;
    }
  }
}