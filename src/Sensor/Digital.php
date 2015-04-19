<?php

namespace Carica\Chip\Sensor {

  use Carica\Io\Event\Emitter;
  use Carica\Io\Device\Pin;

  /**
   * A digital sensor. It read a low/high value from an pin and triggers events if the
   * status changes.
   *
   * @method onChange(callable $callback)
   * @method onHigh(callable $callback)
   * @method onLow(callable $callback)
   * @method onceChange(callable $callback)
   * @method onceHigh(callable $callback)
   * @method onceLow(callable $callback)
   */
  class Digital {

    use Emitter\Aggregation;

    /**
     * @var Pin $_pin
     */
    private $_pin = NULL;

    /**
     * Create object, store pin and attach events
     *
     * @param Pin $pin
     */
    public function __construct(Pin $pin) {
      $this->_pin = $pin;
      $pin->mode = Pin::MODE_INPUT;
      $pin->onChange(
        function () {
          $this->emitEvent($this->isHigh() ? 'high' : 'low', $this);
          $this->emitEvent('change', $this);
        }
      );
    }

    /**
     * Lazy init for the event emitter, they get created if the first
     * event is attached.
     *
     * Define the possible events.
     *
     * @return Emitter
     */
    protected function createEventEmitter() {
      $emitter = new Emitter;
      $emitter->defineEvents(
        array('change', 'high', 'low')
      );
      return $emitter;
    }

    /**
     * Return a string representation of the sensor state
     *
     * @return string
     */
    public function __toString() {
      return $this->isHigh() ? 'high' : 'low';
    }

    /**
     * @return bool
     */
    public function isLow() {
      return !$this->_pin->digital;
    }

    /**
     * @return bool
     */
    public function isHigh() {
      return $this->_pin->digital;
    }
  }
}