<?php

namespace Carica\Chip {

  use Phake;

  abstract class TestCase extends \PHPUnit_Framework_TestCase {

    /**
     * @return \Phake_IMock|\Carica\Firmata\Pin
     */
    protected function getMockForPin() {
      $pin = Phake::mock('\\Carica\\Firmata\\Pin');
      return $pin;
    }
  }
}