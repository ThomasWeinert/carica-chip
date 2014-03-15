<?php

namespace Carica\Chip\Sensor {

  use Carica\Io\Event\Emitter;
  use Carica\Firmata;

  /**
   * Class Analog
   *
   * @method onChange(callable $callback) attach a change callback
   * @method onHigh(callable $callback) attach a callback if the value changes to high
   * @method onLow(callable $callback) attach a call if the value changes to low
   */
  class Digital {

    use Emitter\Aggregation {
      Emitter\Aggregation::callEmitter as __call;
    }

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
          $this->emitEvent($this->isHigh() ? 'high' : 'low', $this);
          $this->emitEvent('change', $this);
        }
      );
    }

    protected function createEventEmitter() {
      $emitter = new Emitter;
      $emitter->defineEvents(
        array('change', 'high', 'low')
      );
      return $emitter;
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
