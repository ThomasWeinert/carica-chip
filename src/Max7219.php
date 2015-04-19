<?php

namespace Carica\Chip {

  use Carica\Io;
  use Carica\Io\Deferred;
  use Carica\Io\Event;
  use Carica\Io\Device\ShiftOut;

  abstract class Max7219 {

    const INDEX_ALL = -1;

    const MODE_DECODE = 0x09;
    const MODE_INTENSITY = 0x0A;
    const MODE_SCAN_LIMIT = 0x0B;
    const MODE_POWER = 0x0C;
    const MODE_TEST = 0x0F;
    const MODE_NOOP = 0x00;

    const VALUE_TRUE = 0x01;
    const VALUE_FALSE = 0x00;

    const DECODE_NONE = 0x00;
    const DECODE_B = 0xFF;

    /**
     * @var int
     */
    private $_index = self::INDEX_ALL;

    /**
     * @var ShiftOut|null
     */
    private $_shiftOut = null;

    /**
     * @var int
     */
    private $_count = 1;

    /**
     * Display is started (initialized)
     * @var bool
     */
    private $_started = FALSE;

    public function __construct(
      ShiftOut $shiftOut
    ) {
      $this->_shiftOut = $shiftOut;
    }

    /**
     * @return ShiftOut
     */
    public function getShiftOut() {
      return $this->_shiftOut;
    }

    /**
     * Sets the index of the matrix if here is more then one connected.
     * Set it to self::INDEX_ALL to controll all connected displays.
     *
     * @param int $index
     * @param int $count
     */
    public function setIndex($index, $count = 1) {
      $this->_index = $index;
      $this->_count = $count;
      $this->_started = FALSE;
    }

    /**
     * Transfer a command to the display hardware
     *
     * @param int $address
     * @param int $value
     */
    protected function sendCommand($address, $value) {
      $this->startUp();
      $shiftOut = $this->getShiftOut();
      $shiftOut->begin();
      if ($this->_index == self::INDEX_ALL) {
        for ($i = 0; $i < $this->_count; $i++) {
          $shiftOut->transfer([$address, $value]);
        }
      } elseif ($this->_index < $this->_count) {
        for ($i = $this->_count - 1; $i > $this->_index; $i--) {
          $shiftOut->transfer([self::MODE_NOOP, self::MODE_NOOP]);
        }
        $this->_shiftOut->transfer([$address, $value]);
        for ($i = $this->_index; $i > 0; $i--) {
          $shiftOut->transfer([self::MODE_NOOP, self::MODE_NOOP]);
        }
      }
      $shiftOut->end();
    }

    /**
     * Start the display. This is a lazy init triggered by transfer().
     */
    private function startUp() {
      if (!$this->_started) {
        $this->_started = TRUE;
        $this->sendCommand(self::MODE_TEST, self::VALUE_FALSE);
        $this->sendCommand(self::MODE_POWER, self::VALUE_FALSE);
        $this->sendCommand(self::MODE_SCAN_LIMIT, 7);
        $this->sendCommand(self::MODE_DECODE, self::DECODE_NONE);
      }
    }

    /**
     * Activate the display
     * @return $this
     */
    public function on() {
      $this->sendCommand(self::MODE_POWER, self::VALUE_TRUE);
      return $this;
    }

    /**
     * Dectivate the display
     * @return $this
     */
    public function off() {
      $this->sendCommand(self::MODE_POWER, self::VALUE_FALSE);
      return $this;
    }

    /**
     * Set the brightness from lowest to highest value
     *
     * @param float $value
     * @return $this
     */
    public function brightness($value) {
      $max = 15;
      $value = round($max * $value);
      if ($value < 0) {
        $value = 0;
      } elseif ($value > $max) {
        $value = $max;
      }
      $this->sendCommand(self::MODE_INTENSITY, (int)$value);
      return $this;
    }

    /**
     * Set all segments off
     *
     * @return $this
     */
    public function clear() {
      for ($i = 1; $i <= 8; $i++) {
        $this->sendCommand($i, 0x00);
      }
      return $this;
    }
  }
}