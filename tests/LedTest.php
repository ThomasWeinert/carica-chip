<?php

namespace Carica\Chip {

  include_once(__DIR__ . '/Bootstrap.php');

  use Phake;

  use Carica\Io\Device\Pin;
  use Carica\Io\Event\Loop\Clock;

  class LedTest extends TestCase {

    /**
     * @covers Carica\Chip\Led::__construct
     */
    public function testConstructor() {
      $pin = $this->getMockForPin();
      Phake::when($pin)->supports(Pin::MODE_PWM)->thenReturn(FALSE);

      $led = new Led($pin);
      $this->assertAttributeSame(
        $pin, '_pin', $led
      );
      $this->assertFalse(
        $led->isDimmable()
      );

      Phake::verify($pin)->setMode(Pin::MODE_OUTPUT);
      Phake::verify($pin)->getDigital();
    }

    /**
     * @covers Carica\Chip\Led::__construct
     */
    public function testConstructorForPwmPin() {
      $pin = $this->getMockForPin();
      Phake::when($pin)->supports(Pin::MODE_PWM)->thenReturn(TRUE);
      Phake::when($pin)->getAnalog()->thenReturn(0.5);

      $led = new Led($pin);
      $this->assertTrue(
        $led->isDimmable()
      );

      Phake::verify($pin)->setMode(Pin::MODE_PWM);
      Phake::verify($pin)->getAnalog();
    }

    /**
     * @covers Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingTrue() {
      $pin = $this->getMockForPin();
      Phake::when($pin)->getDigital()->thenReturn(TRUE);

      $led = new Led($pin);
      $this->assertTrue($led->isOn());
    }

    /**
     * @covers Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingFalse() {
      $pin = $this->getMockForPin();
      Phake::when($pin)->getDigital()->thenReturn(FALSE);

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

      Phake::verify($pin)->setMode(Pin::MODE_OUTPUT);
      Phake::verify($pin)->setDigital(TRUE);
    }

    /**
     * @covers Carica\Chip\Led::off
     */
    public function testOff() {
      $pin = $this->getMockForPin();

      $led = new Led($pin);
      $led->off();

      Phake::verify($pin)->setMode(Pin::MODE_OUTPUT);
      Phake::verify($pin)->setDigital(FALSE);
    }

    /**
     * @covers Carica\Chip\Led::strobe
     */
    public function testStrobeChanges3TimesIn3Seconds() {
      $pin = $this->getMockForPin();
      Phake::when($pin)
        ->getDigital()
        ->thenReturn(FALSE)
        ->thenReturn(FALSE)
        ->thenReturn(TRUE)
        ->thenReturn(FALSE);

      $loop = new Clock();
      $led = new Led($pin);
      $led->loop($loop);
      $led->strobe()->on();
      $loop->tick(3000);

      $on = Phake::verify($pin, Phake::times(2))->setDigital(TRUE);
      $off = Phake::verify($pin)->setDigital(FALSE);
      Phake::inOrder(
        $on, $off, $on
      );
    }

    /**
     * @covers Carica\Chip\Led::stop
     */
    public function testStopTimer() {
      $pin = $this->getMockForPin();

      $loop = new Clock();
      $led = new Led($pin);
      $led->loop($loop);
      $led->strobe()->on();
      $led->stop();
      $loop->tick(3000);

      Phake::verify($pin, Phake::never())->setMode();
    }
  }
}
