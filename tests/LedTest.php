<?php

namespace Carica\Chip {

  include_once(__DIR__ . '/Bootstrap.php');

  use Phake;

  use Carica\Io\Device\Pin;
  use Carica\Io\Event\Loop\Clock;

  class LedTest extends TestCase {

    /**
     * @covers \Carica\Chip\Led::__construct
     */
    public function testConstructor() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->any())
        ->method('supports')
        ->with(Pin::MODE_PWM)
        ->willReturn(FALSE);
      $pin
        ->expects($this->once())
        ->method('setMode')
        ->with(Pin::MODE_OUTPUT);
      $pin
        ->expects($this->once())
        ->method('getDigital');

      $led = new Led($pin);
      $this->assertAttributeSame(
        $pin, '_pin', $led
      );
      $this->assertFalse(
        $led->isDimmable()
      );
    }

    /**
     * @covers \Carica\Chip\Led::__construct
     */
    public function testConstructorForPwmPin() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->any())
        ->method('supports')
        ->with(Pin::MODE_PWM)
        ->willReturn(TRUE);
      $pin
        ->expects($this->once())
        ->method('getAnalog')
        ->willReturn(0.5);
      $pin
        ->expects($this->once())
        ->method('setMode')
        ->with(Pin::MODE_PWM);

      $led = new Led($pin);
      $this->assertTrue(
        $led->isDimmable()
      );
    }

    /**
     * @covers \Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingTrue() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->any())
        ->method('getDigital')
        ->willReturn(TRUE);

      $led = new Led($pin);
      $this->assertTrue($led->isOn());
    }

    /**
     * @covers \Carica\Chip\Led::isOn
     */
    public function testIsOnExpectingFalse() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->any())
        ->method('getDigital')
        ->willReturn(FALSE);

      $led = new Led($pin);
      $this->assertFalse($led->isOn());
    }

    /**
     * @covers \Carica\Chip\Led::on
     */
    public function testOn() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->once())
        ->method('setMode')
        ->with(Pin::MODE_OUTPUT);
      $pin
        ->expects($this->once())
        ->method('setDigital')
        ->with(TRUE);

      $led = new Led($pin);
      $led->on();
    }

    /**
     * @covers \Carica\Chip\Led::off
     */
    public function testOff() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->once())
        ->method('setMode')
        ->with(Pin::MODE_OUTPUT);
      $pin
        ->expects($this->once())
        ->method('setDigital')
        ->with(FALSE);

      $led = new Led($pin);
      $led->off();
    }

    /**
     * @covers \Carica\Chip\Led::strobe
     */
    public function testStrobeChanges3TimesIn3Seconds() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->any())
        ->method('getDigital')
        ->willReturnOnConsecutiveCalls(FALSE, FALSE, TRUE, FALSE);
      $pin
        ->expects($this->exactly(3))
        ->method('setDigital')
        ->withConsecutive([TRUE], [FALSE], [TRUE]);

      $loop = new Clock();
      $led = new Led($pin);
      $led->loop($loop);
      $led->strobe()->on();
      $loop->tick(3000);
    }

    /**
     * @covers \Carica\Chip\Led::stop
     */
    public function testStopTimer() {
      $pin = $this->getMockForPin();
      $pin
        ->expects($this->once())
        ->method('setMode');
      $pin
        ->expects($this->never())
        ->method('setDigital');

      $loop = new Clock();
      $led = new Led($pin);
      $led->loop($loop);
      $led->strobe()->on();
      $led->stop();
      $loop->tick(3000);
    }
  }
}
