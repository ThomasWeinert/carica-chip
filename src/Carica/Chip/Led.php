<?php

namespace Carica\Chip {

  use Carica\Firmata;
  use Carica\Io\Event;

  class Led {

    use Event\Loop\Aggregation;

    private $_board = NULL;
    private $_pin = 0;
    private $_timer = NULL;

    public function __construct(Firmata\Board $board, $pin) {
      $this->_board = $board;
      $this->_pin = (int)$pin;
    }

    private function getPin() {
      return $this->_board->pins[$this->_pin];
    }

    public function isOn() {
      return $this->getPin()->value > 0;
    }

    public function on() {
      $pin = $this->getPin();
      $pin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      $pin->digital = TRUE;
    }

    public function off() {
      $pin = $this->getPin();
      $pin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      $pin->digital = FALSE;
    }

    public function brightness($brightness = 0) {
      $pin = $this->getPin();
      $pin->mode = Firmata\Board::PIN_MODE_PWM;
      $pin->analog = $brightness / 255;
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