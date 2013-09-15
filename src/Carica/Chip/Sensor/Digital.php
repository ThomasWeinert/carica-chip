<?php

namespace Carica\Chip\Sensor {

  use Carica\Io;
  use Carica\Firmata;

  class Digital implements Changeable {

    use ChangeCallbacks;

    /**
     * @var Firmata\Board $_board
     */
    private $_board = NULL;

    /**
     * @var integer $_pin
     */
    private $_pin = 0;

    public function __construct(Firmata\Board $board, $pin) {
      $this->_board = $board;
      $this->_pin = $pin;
      $this->_pin->mode = Firmata\Board::PIN_MODE_INPUT;
      $board->digitalRead(
        $pin,
        function () {
          $this->callbacks()->fire($this);
        }
      );
    }

    public function __toString() {
      return (string)$this->_board->pins[$this->_pin]->digital;
    }
  }
}
