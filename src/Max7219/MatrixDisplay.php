<?php

namespace Carica\Chip\Max7219 {

  use Carica\Chip\Max7219\MatrixDisplay\Row;
  use Carica\Firmata;
  use Carica\Io\Event;

  class MatrixDisplay
    extends \Carica\Chip\Max7219
    implements \ArrayAccess {

    private $_rows = [];

    private $_dots = [
      0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0
    ];

    private $_bits = [1, 2, 4, 8, 16, 32, 64, 128];

    public function __construct(
      Firmata\Board $board, $dataPin, $clockPin, $latchPin
    ) {
      parent::__construct(
        $board, $dataPin, $clockPin, $latchPin
      );
      $this->setUp();
    }

    private function setUp() {
      for ($y = 0; $y < 8; $y++) {
        $this->_rows[] = $row = new Row($this, $y);
      }
    }
    public function offsetExists($offset) {
      return ($offset >= 0 && $offset < 8);
    }

    public function offsetGet($offset) {
      if ($this->offsetExists($offset)) {
        return $this->_rows[$offset];
      }
      return FALSE;
    }

    public function offsetSet($offset, $value) {
      throw new \LogicException('Not a valid row');
    }

    public function offsetUnset($offset) {
      throw new \LogicException('Not a valid row');
    }

    public function getDot($x, $y) {
      if ($position = $this->getPosition($x, $y)) {
        $bit = $this->_bits[$x];
        return ($this->_dots[$y] & $bit) == $bit;
      }
      return FALSE;
    }

    public function setDot($x, $y, $active) {
      if ($bytes = $this->change($x, $y, $active)) {
        $this->update($bytes);
      }
    }

    private function change($x, $y, $active) {
      if ($position = $this->getPosition($x, $y)) {
        $bit = $this->_bits[$x];
        if ($active) {
          $value = $this->_dots[$y] | $bit;
        } else {
          $value = $this->_dots[$y] & ~$bit;
        }
        if ($value !== $this->_dots[$y]) {
          $this->_dots[$y] = $value;
          return [$y => $value];
        }
      }
      return [];
    }

    private function update(array $byteIndex) {
      foreach ($byteIndex as $index => $value) {
        $this->transfer($index + 1, $value);
      }
    }

    private function getPosition($x, $y) {
      if ($x >= 0 && $y >= 0 && $x < 8 && $y < 8) {
        return [$x, $y];
      }
      return FALSE;
    }
  }
}