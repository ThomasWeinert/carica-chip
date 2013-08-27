<?php

namespace Carica\Chip\Sensor {

  use Carica\Io;
  use Carica\Firmata;

  class Analog implements Changeable {

    use ChangeCallbacks;


    /**
     * @var Carica\Firmata\Board $_board
     */
    private $_board = NULL;

    /**
     * @var Carica\firmata\Pin $_pin
     */
    private $_pin = 0;

    /**
     * @var Carica\Io\Callbacks $_callbacks
     */
    private $_callbacks = NULL;

    public function __construct(Firmata\Board $board, $pin) {
      $this->_board = $board;
      $this->_pin = $this->_board->pins[$pin];
      $this->_pin->mode = Firmata\Board::PIN_MODE_ANALOG;
      $board->analogRead(
        $pin,
        function ($value) {
          $this->callbacks()->fire($this);
        }
      );
    }

    public function getValue() {
      return $this->_pin->getValue();
    }

    public function __toString() {
      return number_format($this->_pin->analog, 6, '.', ',');
    }
  }
}
