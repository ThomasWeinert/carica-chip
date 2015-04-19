<?php

namespace Carica\Chip\Sensor {

  use Carica\Io\Event\Emitter;
  use Carica\Io\Device\Pin;

  /**
   * An analog sensor, returns a value between 0 and 1 and triggers an event if it changes.
   *
   * @method onChange(callable $callback) attach a change callback
   * @method onceChange(callable $callback) attach a change callback that is executed once
   */
  class Analog {

    use Emitter\Aggregation;

    /**
     * @var Pin $_pin
     */
    private $_pin = NULL;

    /**
     * Create object, store pin object and attach event
     *
     * @param Pin $pin
     */
    public function __construct(Pin $pin) {
      $this->_pin = $pin;
      $pin->mode = Pin::MODE_ANALOG;
      $pin->onChange(
        function () {
          $this->emitEvent('change', $this);
        }
      );
    }

    /**
     * Lazy create for the event emitter, defines the possible event.
     *
     * @return Emitter
     */
    protected function createEventEmitter() {
      $emitter = new Emitter;
      $emitter->defineEvents(
        array('change')
      );
      return $emitter;
    }

    /**
     * Return the sensor value. This is a value between 0 and 1.
     *
     * @return float
     */
    public function get() {
      return $this->_pin->getAnalog();
    }

    /**
     * Return the current sensor, value as string.
     *
     * @return string
     */
    public function __toString() {
      return number_format($this->_pin->getAnalog(), 6, '.', ',');
    }
  }
}
