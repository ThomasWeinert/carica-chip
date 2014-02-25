<?php

namespace Carica\Chip\Sensor {

  use Carica\Io;
  use Carica\Firmata;

  class Analog implements Changeable {

    use ChangeCallbacks;

    /**
     * @var Firmata\Pin $_pin
     */
    private $_pin = NULL;

    public function __construct(Firmata\Pin $pin) {
      $this->_pin = $pin;
      $this->_pin->mode = Firmata\Board::PIN_MODE_ANALOG;
      $this->_pin->events()->on(
        'change-value',
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
