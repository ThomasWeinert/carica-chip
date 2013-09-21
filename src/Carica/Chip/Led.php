<?php

namespace Carica\Chip {

  use Carica\Firmata;
  use Carica\Io\Event;

  class Led {

    use Event\Loop\Aggregation;

    /**
     * @var Firmata\Pin
     */
    private $_pin = NULL;
    private $_timer = NULL;

    public function __construct(Firmata\Pin $pin) {
      $this->_pin = $pin;
    }

    public function isOn() {
      return $this->_pin->value > 0;
    }

    public function on() {
      $this->_pin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      $this->_pin->digital = TRUE;
    }

    public function off() {
      $this->_pin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      $this->_pin->digital = FALSE;
    }

    public function brightness($brightness = 0) {
      $this->_pin->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_pin->analog = $brightness / 255;
    }

    public function blink($duration = 1000) {
      $that = $this;
      $this->_timer = $this
        ->loop()
        ->setInterval(
          function () use ($that) {
            $that->isOn() ? $that->off() : $that->on();
          },
          $duration
        );
    }

    public function stop() {
      if (isset($this->_timer)) {
        $this->loop()->remove($this->_timer);
      }
    }
  }
}