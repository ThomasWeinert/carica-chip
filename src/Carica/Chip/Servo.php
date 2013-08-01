<?php

namespace Carica\Chip {

  use Carica\Firmata;
  use Carica\Io\Event;

  class Servo {

    use Event\Loop\Aggregation;

    private $_board = NULL;
    private $_pin = 0;
    private $_range = 180;
    private $_invert = FALSE;

    public function __construct(Firmata\Board $board, $pin, $range = 180) {
      $this->_board = $board;
      $this->_pin = (int)$pin;
      if (abs($range) > 255) {
        throw new \InvalidArgumentException('Invalid servo range: '.(int)$range);
      } elseif ($range < 0) {
        $this->_invert = TRUE;
      }
      $this->_range = abs($range);
    }

    public function setDegrees($value) {
      $this->_board->pins[$this->_pin]->mode = Firmata\PIN_STATE_SERVO;
      $value = ($this->_invert)  ? $this->_range - $value : $value;
      $this->_board->pins[$this->_pin]->analog = $value;
    }

    public function getDegrees() {
      $value = $this->_board->pins[$this->_pin]->analog;
      return ($this->_invert)  ? $this->_range - $value : $value;
    }
  }
}