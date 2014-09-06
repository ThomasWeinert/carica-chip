<?php

namespace Carica\Chip\Max7219 {

  use Carica\Chip\Max7219\MatrixDisplay\Row;
  use Carica\Firmata;

  class MatrixDisplay
    extends \Carica\Chip\Max7219
    implements \ArrayAccess {

    const ROTATION_NONE = 0;
    const ROTATION_CW_90 = 1;
    const ROTATION_CW_180 = 2;
    const ROTATION_CW_270 = 3;

    const ROTATION_CCW_90 = self::ROTATION_CW_270;
    const ROTATION_CCW_180 = self::ROTATION_CW_180;
    const ROTATION_CCW_270 = self::ROTATION_CW_90;

    private $_rows = [];

    private $_dots = [
      0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0
    ];
    private $_modified = [];

    private $_bits = [1, 2, 4, 8, 16, 32, 64, 128];

    private $_rotation = self::ROTATION_NONE;

    public function __construct(
      Firmata\Board $board, $dataPin, $clockPin, $latchPin, $rotation = self::ROTATION_NONE
    ) {
      parent::__construct(
        $board, $dataPin, $clockPin, $latchPin
      );
      for ($y = 0; $y < 8; $y++) {
        $this->_rows[] = $row = new Row($this, $y);
      }
      $this->_rotation = $rotation;
    }

    /**
     * @return array
     */
    public function getBytes() {
      return $this->_dots;
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset) {
      return ($offset >= 0 && $offset < 8);
    }

    /**
     * @param int $offset
     * @return Row
     */
    public function offsetGet($offset) {
      if ($this->offsetExists($offset)) {
        return $this->_rows[$offset];
      }
      return FALSE;
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
      throw new \LogicException('Not a valid row');
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset) {
      throw new \LogicException('Not a valid row');
    }

    /**
     * Set all dots to off
     *
     * @return $this
     */
    public function clear() {
      for ($i = 1; $i <= 8; $i++) {
        $this->transfer($i, 0);
      }
      $this->_dots = [
        0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0
      ];
      $this->_modified = [];
      return $this;
    }

    /**
     * Get the status of a dot by its coordinates
     *
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function getDot($x, $y) {
      if ($position = $this->getPosition($x, $y)) {
        $bit = $this->_bits[$x];
        return ($this->_dots[$y] & $bit) == $bit;
      }
      return FALSE;
    }

    /**
     * Set the status of a dot by its coordinates.
     *
     * @param int $x
     * @param int $x
     * @param bool $active
     * @param bool $implicitCommit
     */
    public function setDot($x, $y, $active, $implicitCommit = TRUE) {
      $this->modify($x, $y, $active);
      $this->implicitCommit($implicitCommit);
    }

    /**
     * Set the dots of a specified row
     *
     * @param int $x
     * @param bool|int|bool[]|int[] $dots
     * @param bool $implicitCommit
     */
    public function setRow($y, $dots, $implicitCommit = TRUE) {
      $active = $this->expandDotsArgument($dots);
      for ($x = 0; $x < 8; $x++) {
        $this->modify($x, $y, isset($active[$x]));
      }
      $this->implicitCommit($implicitCommit);
    }

    /**
     * Set the dots of the specified column
     *
     * @param int $x
     * @param bool|int|bool[]|int[] $dots
     * @param bool $implicitCommit
     */
    public function setColumn($x, $dots, $implicitCommit = TRUE) {
      $active = $this->expandDotsArgument($dots);
      for ($y = 0; $y < 8; $y++) {
        $this->modify($x, $y, isset($active[$y]));
      }
      $this->implicitCommit($implicitCommit);
    }

    /**
     * Expand the $dots argument into an array with the dot index as key. Value
     * if always TRUE.
     *
     * The argument can be:
     *
     *   A simple boolean representing the status of all
     *   dots.
     *
     *   An integer will be interpreted as a bit mask.
     *
     *   An array of integers will that contains the indizes of the
     *   active dots.
     *
     *   An array of booleans with the status of each dot.
     *
     * @param int|bool|bool[]|int[]
     * @arrau TRUE[]
     */
    private function expandDotsArgument($dots) {
      $result = [];
      if (is_array($dots)) {
        $result = [];
        foreach ($dots as $key => $value) {
          if (is_bool($value) && $value) {
            $result[$key] = TRUE;
          } elseif (is_int($value)) {
            $result[$value] = TRUE;
          }
        }
      } elseif (is_bool($dots) && $dots) {
        $result = [];
        for ($i = 0; $i < 8; $i++) {
          $result[$i] = TRUE;
        }
      } elseif (is_int($dots)) {
        $result = [];
        foreach ($this->_bits as $index => $bit) {
          if (($dots & $bit) == $bit) {
            $result[$index] = TRUE;
          }
        }
      }
      return $result;
    }

    /**
     * Modify a dot status. But do not send it to the hardware.
     *
     * @param int $x
     * @param int $y
     * @param bool $active
     * @return bool
     */
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

    /**
     * Send the status to the hardware.
     *
     * @param bool $forceAll update all, not only the modified.
     */
    public function commit($forceAll = FALSE) {
      $rows = array_keys($forceAll ? $this->_dots : $this->_modified);
      foreach ($rows as $index) {
        $this->transfer($index + 1, $this->_dots[$index]);
      }
      $this->_modified = [];
    }

    /**
     * An check if the trigger was set and send the status if set.
     *
     * @param $trigger
     */
    private function implicitCommit($trigger) {
      if ($trigger) {
        $this->commit();
      }
    }

    /**
     * Convert the $x,$y coordinates into the internal position. This
     * allows a mapping depending on the rotation.
     *
     * @param int $x
     * @param int $y
     * @return FALSE|int[]
     */
    private function getPosition($x, $y) {
      if ($x >= 0 && $y >= 0 && $x < 8 && $y < 8) {
        switch ($this->_rotation) {
        case self::ROTATION_CW_90 :
          return [7 - $x, $y];
          break;
        case self::ROTATION_CW_180 :
          return [7 - $y, 7 - $x];
        case self::ROTATION_CW_270 :
          return [$x, 7 - $y];
        default :
          return [$y, $x];
        }
      }
      return FALSE;
    }
  }
}