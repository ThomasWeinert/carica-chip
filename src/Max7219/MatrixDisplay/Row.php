<?php

namespace Carica\Chip\Max7219\MatrixDisplay {

  use Carica\Firmata;
  use Carica\Io\Event\Emitter;

  /**
   *
   * @method onChange(callable $callback) attach a change callback
   * @method onceChange(callable $callback) attach a change callback that is executed once
   */
  class Row implements \ArrayAccess {

    use Emitter\Aggregation;

    private $_columns = [
      1, 2, 4, 8, 16, 32, 64, 128
    ];

    private $_value = 0;

    /**
     * Get the byte value of the segment
     *
     * @return int
     */
    public function getValue() {
      return $this->_value;
    }

    public function offsetExists($offset) {
      return ($offset >= 0 && $offset < 8);
    }

    public function offsetGet($offset) {
      if ($this->offsetExists($offset)) {
        $bit = $this->_columns[$offset];
        return ($this->_value & $bit) == $bit;
      }
      return FALSE;
    }

    public function offsetSet($offset, $active) {
      if ($this->offsetExists($offset)) {
        $bit = $this->_columns[$offset];
        if ($active) {
          $value = $this->_value | $bit;
        } else {
          $value = $this->_value & ~$bit;
        }
        if ($value !== $this->_value) {
          $this->_value = $value;
          $this->emitEvent('change', $this);
        }
      }
    }

    public function offsetUnset($offset) {
      $this->offsetSet($offset, FALSE);
    }
  }
}