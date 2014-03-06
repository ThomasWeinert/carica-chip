<?php

namespace Carica\Chip {

  include_once(__DIR__ . '/Bootstrap.php');

  use Carica\Firmata\Board;

  class LedTest extends TestCase {

    /**
     * @covers Carica\Chip\Led::__construct
     */
    public function testConstructor() {
      $pin = $this->getMockForPin();
      $led = new Led($pin);
      $this->assertAttributeSame(
        $pin, '_pin', $led
      );
    }

    /**
     * @covers Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingTrue() {
      $pin = $this->getMockForPin();
      $led = new Led($pin);
      $pin
        ->expects($this->once())
        ->method('__get')
        ->with('value')
        ->will($this->returnValue(23));
      $this->assertTrue($led->isOn());
    }

    /**
     * @covers Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingFalse() {
      $pin = $this->getMockForPin();
      $led = new Led($pin);
      $pin
        ->expects($this->once())
        ->method('__get')
        ->with('value')
        ->will($this->returnValue(0));
      $this->assertFalse($led->isOn());
    }

    /**
     * @covers Carica\Chip\Led::on
     */
    public function testOn() {
      $pin = $this->getMockForPin();
      $led = new Led($pin);
      $pin
        ->expects($this->exactly(2))
        ->method('__set')
        ->getMatcher()
        ->parametersMatcher = $this->withConsecutive(
          ['mode', Board::PIN_MODE_OUTPUT],
          ['digital', TRUE]
        );
      $led->on();
    }

    /**
     * @covers Carica\Chip\Led::off
     */
    public function testOff() {
      $pin = $this->getMockForPin();
      $led = new Led($pin);
      $pin
        ->expects($this->exactly(2))
        ->method('__set')
        ->getMatcher()
        ->parametersMatcher = $this->withConsecutive(
          ['mode', Board::PIN_MODE_OUTPUT],
          ['digital', FALSE]
        );
      $led->off();
    }

    /**
     * @covers Carica\Chip\Led::blink
     */
    public function testBlinkChanges3TimesIn3Seconds() {
      $loop = new \Carica\Io\Event\Loop\Clock();
      $pin = $this->getMockForPin();
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

    /**
     * @covers Carica\Chip\Led::stop
     */
    public function testStopBlinking() {
      $loop = new \Carica\Io\Event\Loop\Clock();
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->never())
        ->method('__set');

      $led = new Led($pin);
      $led->loop($loop);
      $led->blink();
      $led->stop();
      $loop->tick(3000);
    }
  }
}
