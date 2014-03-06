<?php

namespace Carica\Chip {

  include_once(__DIR__ . '/Bootstrap.php');

  use Carica\Firmata\Board;

  class LedTest extends TestCase {

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
        ->expects($this->any())
        ->method('__set')
        ->getMatcher()
        ->parametersMatcher = $this->withConsecutive(
          ['mode', Board::PIN_MODE_OUTPUT],
          ['digital', TRUE],
          ['mode', Board::PIN_MODE_OUTPUT],
          ['digital', FALSE],
          ['mode', Board::PIN_MODE_OUTPUT],
          ['digital', TRUE]
        );

      $led = new Led($pin);
      $led->loop($loop);
      $led->blink();
      $loop->tick(3000);
    }
  }
}
