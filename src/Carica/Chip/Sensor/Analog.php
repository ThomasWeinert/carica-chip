<?php

namespace Carica\Chip\Sensor {

  use Carica\Io;
  use Carica\Firmata;

  class Analog implements Changeable {

    use ChangeCallbacks;

    /**
     * @var Firmata\Board $_board
     */
    private $_board = NULL;

    /**
     * @var Firmata\Pin $_pin
     */
    private $_pin = 0;

    public function __construct(Firmata\Board $board, $pin) {
      $this->_board = $board;
      $this->_pin = $this->_board->pins[$pin];
      $this->_pin->mode = Firmata\Board::PIN_MODE_ANALOG;
      $board->analogRead(
        $pin,
        function () {
          $this->callbacks()->fire($this);
        }
      );
    }

    public function get() {
      return $this->_pin->analog;
    }

    public function __toString() {
      return number_format($this->_pin->analog, 6, '.', ',');
    }
  }
}
