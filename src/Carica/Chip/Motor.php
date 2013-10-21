<?php

namespace Carica\Chip {

  use Carica\Firmata;

  class Motor {

    private $_speedPin = NULL;
    private $_directionPin = NULL;
    private $_reverseDirectionPin = NULL;

    public function __construct(
      Firmata\Pin $speedPin,
      Firmata\Pin $directionPin = NULL,
      Firmata\Pin $reverseDirectionPin = NULL) {
      $this->_speedPin = $speedPin;
      $this->_directionPin = $directionPin;
      $this->_reverseDirectionPin = $reverseDirectionPin;

    }

    public function forward($speed) {
      $this->setPinModes();
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->digital = FALSE;
        if (NULL !== $this->_reverseDirectionPin) {
          $this->_directionPin->digital = !$this->_directionPin->digital;
        }
      }
      $this->_speedPin->analog($speed);
    }

    public function backward($speed) {
      $this->setPinModes();
      if (NULL == $this->_directionPin) {
        throw new LogicException('No direction pin(s) provided.');
      }
      $this->_directionPin->digital = TRUE;
      if (NULL !== $this->_reverseDirectionPin) {
        $this->_directionPin->digital = !$this->_directionPin->digital;
      }
      $this->_speedPin->analog($speed);
    }

    public function stop() {
      $this->setPinModes();
      $this->_speedPin->analog(0);
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->digital = FALSE;
        if (NULL !== $this->_reverseDirectionPin) {
          $this->_directionPin->digital = FALSE;
        }
      }
    }

    private function setPinModes() {
      $this->_speedPin->mode = Firmata\Board::PIN_MODE_PWM;
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      }
      if (NULL !== $this->_reverseDirectionPin) {
        $this->_directionPin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      }
    }

  }
}
