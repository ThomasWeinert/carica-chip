<?php

namespace Carica\Chip\Sensor {

  use Carica\Io\Event\Emitter;
  use Carica\Firmata;

  /**
   * Class Analog
   *
   * @method onChange(callable $callback) attach a change callback
   */
  class Analog {

    use Emitter\Aggregation {
      Emitter\Aggregation::callEmitter as __call;
    }

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
          $this->emitEvent('change', $this);
        }
      );
    }

    protected function createEventEmitter() {
      $emitter = new Emitter;
      $emitter->defineEvents(
        array('change')
      );
      return $emitter;
    }

    public function get() {
      return $this->_pin->analog;
    }

    public function __toString() {
      return number_format($this->_pin->analog, 6, '.', ',');
    }
  }
}
