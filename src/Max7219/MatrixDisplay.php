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
    private $_modified = [];

    private $_bits = [1, 2, 4, 8, 16, 32, 64, 128];

    public function __construct(
      Firmata\Board $board, $dataPin, $clockPin, $latchPin
    ) {
      parent::__construct(
        $board, $dataPin, $clockPin, $latchPin
      );
      for ($y = 0; $y < 8; $y++) {
        $this->_rows[] = $row = new Row($this, $y);
      }
      $this->clear();
      $this->off();
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

    public function clear() {
      for ($i = 1; $i <= 8; $i++) {
        $this->transfer($i, 0);
      }
      $this->_dots = [
        0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0
      ];
      $this->_modified = [];
    }

    public function getDot($x, $y) {
      if ($position = $this->getPosition($x, $y)) {
        $bit = $this->_bits[$x];
        return ($this->_dots[$y] & $bit) == $bit;
      }
      return FALSE;
    }

    public function setDot($x, $y, $active, $implicitCommit = TRUE) {
      $this->modify($x, $y, $active);
      $this->implicitCommit($implicitCommit);
    }

    public function setRow($y, $dots, $implicitCommit = TRUE) {
      $active = $this->expandDotsArgument($dots);
      for ($x = 0; $x < 8; $x++) {
        $this->modify($x, $y, isset($active[$x]));
      }
      $this->implicitCommit($implicitCommit);
    }

    public function setColumn($x, $dots, $implicitCommit = TRUE) {
      $active = $this->expandDotsArgument($dots);
      for ($y = 0; $y < 8; $y++) {
        $this->modify($x, $y, isset($active[$y]));
      }
      $this->implicitCommit($implicitCommit);
    }

    private function expandDotsArgument($dots) {
      if (is_array($dots)) {
        return array_flip($dots);
      } elseif (is_bool($dots) && $dots) {
        $result = [];
        for ($i = 0; $i < 8; $i++) {
          $result[$i] = TRUE;
        }
        return $result;
      } elseif (is_int($dots)) {
        $result = [];
        foreach ($this->_bits as $index => $bit) {
          if (($dots & $bit) == $bit) {
            $result[$index] = TRUE;
          }
        }
        return $result;
      }
      return [];
    }

    private function modify($x, $y, $active) {
      if ($position = $this->getPosition($x, $y)) {
        $row = $position[1];
        $bit = $this->_bits[$position[0]];
        if ($active) {
          $value = $this->_dots[$row] | $bit;
        } else {
          $value = $this->_dots[$row] & ~$bit;
        }
        if ($value !== $this->_dots[$row]) {
          $this->_dots[$row] = $value;
          $this->_modified[$row] = TRUE;
          return TRUE;
        }
      }
      return FALSE;
    }

    public function commit($forceAll = FALSE) {
      $rows = array_keys($forceAll ? $this->_dots : $this->_modified);
      foreach ($rows as $index) {
        $this->transfer($index + 1, $this->_dots[$index]);
      }
      $this->_modified = [];
    }

    private function implicitCommit($trigger) {
      if ($trigger) {
        $this->commit();
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