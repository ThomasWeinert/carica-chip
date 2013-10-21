<?php

namespace Carica\Chip\Sensor {

  use Carica\Io;
  use Carica\Firmata;

  class Digital implements Changeable {

    use ChangeCallbacks;

    /**
     * @var Firmata\Pin $_pin
     */
    private $_pin = NULL;

    public function __construct(Firmata\Pin $pin) {
      $this->_pin = $pin;
      $this->_pin->mode = Firmata\Board::PIN_MODE_INPUT;
      $this->_pin->events()->on(
        'change-value',
        function () {
          $this->callbacks()->fire($this);
        }
      );
    }

    public function __toString() {
      return $this->isHigh() ? 'high' : 'low';
    }

    public function isLow() {
      return !$this->_pin->digital;
    }

    public function isHigh() {
      return $this->_pin->digital;
    }
  }
}
