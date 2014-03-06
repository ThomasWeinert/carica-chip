<?php

namespace Carica\Chip {

  include_once(__DIR__ . '/Bootstrap.php');

  use Phake;

  use Carica\Firmata\Board;
  use Carica\Io\Event\Loop\Clock;

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
      Phake::when($pin)->__get('value')->thenReturn(23);

      $led = new Led($pin);
      $this->assertTrue($led->isOn());
    }

    /**
     * @covers Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingFalse() {
      $pin = $this->getMockForPin();
      Phake::when($pin)->__get('value')->thenReturn(0);

      $led = new Led($pin);
      $this->assertFalse($led->isOn());
    }

    /**
     * @covers Carica\Chip\Led::on
     */
    public function testOn() {
      $pin = $this->getMockForPin();

      $led = new Led($pin);
      $led->on();

      Phake::verify($pin)->__set('mode', Board::PIN_MODE_OUTPUT);
      Phake::verify($pin)->__set('digital', TRUE);
    }

    /**
     * @covers Carica\Chip\Led::off
     */
    public function testOff() {
      $pin = $this->getMockForPin();

      $led = new Led($pin);
      $led->off();

      Phake::verify($pin)->__set('mode', Board::PIN_MODE_OUTPUT);
      Phake::verify($pin)->__set('digital', FALSE);
    }

    /**
     * @covers Carica\Chip\Led::blink
     */
    public function testBlinkChanges3TimesIn3Seconds() {
      $pin = $this->getMockForPin();
      Phake::when($pin)
        ->__get('value')
        ->thenReturn(0)
        ->thenReturn(1)
        ->thenReturn(0);

      $loop = new Clock();
      $led = new Led($pin);
      $led->loop($loop);
      $led->blink();
      $loop->tick(3000);

      $modeChange = Phake::verify($pin, Phake::times(3))->__set('mode', Board::PIN_MODE_OUTPUT);
      $digitalTrue = Phake::verify($pin, Phake::times(2))->__set('digital', TRUE);
      $digitalFalse = Phake::verify($pin)->__set('digital', FALSE);
      Phake::inOrder(
        $modeChange,
        $digitalTrue,
        $modeChange,
        $digitalFalse,
        $modeChange,
        $digitalTrue
      );
    }

    /**
     * @covers Carica\Chip\Led::stop
     */
    public function testStopBlinking() {
      $pin = $this->getMockForPin();

      $loop = new Clock();
      $led = new Led($pin);
      $led->loop($loop);
      $led->blink();
      $led->stop();
      $loop->tick(3000);

      Phake::verify($pin, Phake::never())->__set();
    }
  }
}
