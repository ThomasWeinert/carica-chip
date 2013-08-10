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
     * @var Carica\Io\Callbacks $_callbacks
     */
    private $_callbacks = NULL;

    public function onChange(Callable $callback) {
      $this->callbacks()->add($callback);
      return $this;
    }

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
