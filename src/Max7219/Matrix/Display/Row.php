<?php

namespace Carica\Chip\Max7219\Matrix\Display {

  use Carica\Chip\Max7219\Matrix\Display;
  use Carica\Io\Event\Emitter;

  /**
   *
   * @method onChange(callable $callback) attach a change callback
   * @method onceChange(callable $callback) attach a change callback that is executed once
   */
  class Row implements \ArrayAccess {

    use Emitter\Aggregation;

    private $_display;

    public function __construct(Display $display, $index) {
      $this->_display = $display;
      $this->_index = $index;
    }

    public function offsetExists($offset) {
      return ($offset >= 0 && $offset < 8);
    }

    public function offsetGet($offset) {
      if ($this->offsetExists($offset)) {
        return $this->_display->getDot($this->_index, $offset);
      }
      return FALSE;
    }

    public function offsetSet($offset, $active) {
      $this->_display->setDot($this->_index, $offset, $active);
    }

    public function offsetUnset($offset) {
      $this->_display->setDot($this->_index, $offset, FALSE);
    }
  }
}