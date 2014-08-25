<?php

namespace Carica\Chip\Max7219 {

  use Carica\Chip\Max7219\SegmentDisplay\Segment;
  use Carica\Firmata;

  class SegmentDisplay implements \ArrayAccess {

    const MODE_DECODE = 0x09;
    const MODE_INTENSITY = 0x0A;
    const MODE_SCAN_LIMIT = 0x0B;
    const MODE_POWER = 0x0C;
    const MODE_TEST = 0x0F;
    const MODE_NOOP = 0x00;

    const VALUE_TRUE = 0x01;
    const VALUE_FALSE = 0x00;

    const DECODE_B = 0xFF;

    /**
     * @var Firmata\Board
     */
    private $_board = NULL;
    /**
     * @var Firmata\Pin
     */
    private $_latch = NULL;
    /**
     * @var int
     */
    private $_dataPin = 0;
    /**
     * @var int
     */
    private $_clockPin = 0;

    /**
     * @var Segment[]
     */
    private $_segments = [];

    /**
     * @param Firmata\Board $board
     * @param int $dataPin
     * @param int $clockPin
     * @param int $latchPin
     */
    public function __construct(
      Firmata\Board $board, $dataPin, $clockPin, $latchPin, $length = 8
    ) {
      $this->_board = $board;
      $this->_latch = $board->pins[$latchPin];
      $this->_latch->mode = Firmata\Pin::MODE_OUTPUT;
      $this->_clockPin = $clockPin;
      $this->_dataPin = $dataPin;
      $board->pins[$clockPin]->mode = Firmata\Pin::MODE_OUTPUT;
      $board->pins[$dataPin]->mode = Firmata\Pin::MODE_OUTPUT;
      $this->setLength($length);
      $this->off();
      $this->transfer(self::MODE_DECODE, 0);
      $this->brightness(0.01);
    }

    private function setLength($length) {
      $this->transfer(self::MODE_SCAN_LIMIT, $length - 1);
      $this->_segments = [];
      for ($i = 0; $i < $length; $i++) {
        $this->_segments[] = $segment = new Segment();
      }
    }

    /**
     * @param int $address
     * @param int $value
     */
    private function transfer($address, $value) {
      echo "0x", dechex($address), "  0x", dechex($value), "\n";
      $this->_latch->digital = FALSE;
      $this->_board->shiftOut($this->_dataPin, $this->_clockPin, [$address, $value]);
      $this->_latch->digital = TRUE;
    }

    public function on() {
      $this->transfer(self::MODE_POWER, self::VALUE_TRUE);
      return $this;
    }

    public function off() {
      $this->transfer(self::MODE_POWER, self::VALUE_FALSE);
      return $this;
    }

    public function brightness($value) {
      if ($value < 0) {
        $value = 0;
      } elseif ($value > 1) {
        $value = 255;
      } else {
        $value = 255 * $value;
      }
      $this->transfer(self::MODE_INTENSITY, $value);
      return $this;
    }

    public function update() {
      foreach ($this->_segments as $index => $segment) {
        $this->transfer($index + 1, $segment->getValue());
      }
      return $this;
    }

    /**
     * Return true if a segment with that index exists
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset) {
      return array_key_exists($offset, $this->_segments);
    }

    /**
     * Get the segment object for a give offset
     * @param int $offset
     * @return \Carica\Chip\Max7219\SegmentDisplay\Segment
     */
    public function offsetGet($offset) {
      return $this->_segments[$offset];
    }

    /**
     * Allow to change the value of an segemnt or assign it from another segment
     * @return void
     */
    public function offsetSet($offset, $value) {
      if (!$this->_segments[$offset]) {
        return;
      }
      if ($value instanceof Segment) {
        $this->_segments[$offset]->setValue($value->getValue());
      } else {
        $this->_segments[$offset]->setValue((int)$value);
      }
    }

    /**
     * Reset the segment value to 0
     *
     * @return void
     */
    public function offsetUnset($offset) {
      if (!$this->_segments[$offset]) {
        return;
      }
      $this->_segments[$offset]->setValue(0);
    }
  }
}