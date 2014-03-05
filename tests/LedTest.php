<?php

namespace Carica\Chip {

  include_once(__DIR__ . '/Bootstrap.php');

  class LedTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Chip\Led::blink()
     */
    public function testBlinkChanges3TimesIn3Seconds() {
      $loop = new \Carica\Io\Event\Loop\Clock();
      $pin = $this
        ->getMockBuilder('\\Carica\\Firmata\\Pin')
        ->disableOriginalConstructor()
        ->getMock();
      $pin
        ->expects($this->any())
        ->method('__get')
        ->with('value')
        ->will(
          $this->onConsecutiveCalls(0, 1, 0)
        );
      $pin
        ->expects($this->at(1))
        ->method('__set')
        ->with('mode', \Carica\Firmata\Board::PIN_MODE_OUTPUT);
      $pin
        ->expects($this->at(2))
        ->method('__set')
        ->with('digital', TRUE);
      $pin
        ->expects($this->at(4))
        ->method('__set')
        ->with('mode', \Carica\Firmata\Board::PIN_MODE_OUTPUT);
      $pin
        ->expects($this->at(5))
        ->method('__set')
        ->with('digital', FALSE);
      $pin
        ->expects($this->at(7))
        ->method('__set')
        ->with('mode', \Carica\Firmata\Board::PIN_MODE_OUTPUT);
      $pin
        ->expects($this->at(8))
        ->method('__set')
        ->with('digital', TRUE);

      $led = new Led($pin);
      $led->loop($loop);
      $led->blink();
      $loop->tick(3000);
    }

  }
}
