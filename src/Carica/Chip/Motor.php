<?php

namespace Carica\Chip {

  use Carica\Firmata;

  class Motor {

    private $_speedPin = NULL;
    private $_directionPin = NULL;

    public function __construct(Firmata\Pin $speedPin, Firmata\Pin $directionPin = NULL) {
      $this->_speedPin = $speedPin;
      $this->_directionPin = $directionPin;
    }

    public function forward($speed) {
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->mode = Firmata\Board::PIN_MODE_OUTPUT;
        $this->_directionPin->digital = FALSE;
      }
      $this->_speedPin->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_speedPin->analog($speed);
    }

    public function backward($speed) {
      if (NULL == $this->_directionPin) {
        throw new LogicException('No direction pin provided.');
      }
      $this->_directionPin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      $this->_directionPin->digital = TRUE;
      $this->_speedPin->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_speedPin->analog($speed);
    }

    public function stop() {
      $this->_speedPin->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_speedPin->analog(0);
    }

  }
}
