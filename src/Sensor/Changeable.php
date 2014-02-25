<?php

namespace Carica\Chip\Sensor {

  use Carica\Io;
  use Carica\Firmata;

  interface Changeable {

    function onChange(callable $callback);

    function callbacks(Io\Callbacks $callbacks = NULL);
  }

  trait ChangeCallbacks {

    /**
     * @var Io\Callbacks $_callbacks
     */
    private $_callbacks = NULL;

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function onChange(Callable $callback) {
      $this->callbacks()->add($callback);
      return $this;
    }

    /**
     * Getter/Setter for the callbacks subobject
     *
     * @param Io\Callbacks $callbacks
     * @return Io\Callbacks
     */
    public function callbacks(Io\Callbacks $callbacks = NULL) {
      if (isset($callbacks)) {
        $this->_callbacks = $callbacks;
      } elseif (NULL == $this->_callbacks) {
        $this->_callbacks = new Io\Callbacks;
      }
      return $this->_callbacks;
    }
  }
}
