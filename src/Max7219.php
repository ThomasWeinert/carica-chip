<?php

namespace Carica\Chip {

  use Carica\Io;
  use Carica\Io\Deferred;
  use Carica\Io\Event;
  use Carica\Firmata;

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

    const DECODE_B = 0xFF;

    /**
     * @var Firmata\Board
     */
    private $_board = NULL;
    /**
     * @var int
     */
    private $_latchPin = NULL;
    /**
     * @var int
     */
    private $_dataPin = 0;
    /**
     * @var int
     */
    private $_clockPin = 0;

    /**
     * @var int
     */
    private $_index = 0;

    /**
     * @var int
     */
    private $_count = 0;

    public function __construct(
      Firmata\Board $board, $dataPin, $clockPin, $latchPin
    ) {
      $this->_board = $board;
      $this->_latchPin = $latchPin;
      $this->_clockPin = $clockPin;
      $this->_dataPin = $dataPin;
      $board->pins[$latchPin]->mode = Firmata\Pin::MODE_OUTPUT;
      $board->pins[$clockPin]->mode = Firmata\Pin::MODE_OUTPUT;
      $board->pins[$dataPin]->mode = Firmata\Pin::MODE_OUTPUT;
      $this->setIndex(0);
    }

    public function setIndex($index, $count = 1) {
      $this->_index = $index;
      $this->_count = $count;
      $this->transfer(self::MODE_POWER, self::VALUE_TRUE);
      $this->transfer(self::MODE_SCAN_LIMIT, 7);
      $this->transfer(self::MODE_DECODE, 0x00);
      $this->clear();
      $this->off();
    }

    /**
     * Transfer a command to the display hardware
     *
     * @param int $address
     * @param int $value
     */
    protected function transfer($address, $value) {
      $this->_board->digitalWrite($this->_latchPin, Firmata\Board::DIGITAL_LOW);
      if ($this->_index == self::INDEX_ALL) {
        for ($i = 0; $i < $this->_count; $i++) {
          $this->_board->shiftOut($this->_dataPin, $this->_clockPin, [$address, $value]);
        }
      } elseif ($this->_index < $this->_count) {
        for ($i = 0; $i < $this->_index; $i++) {
          $this->_board->shiftOut(
            $this->_dataPin, $this->_clockPin, [self::MODE_NOOP, self::MODE_NOOP]
          );
        }
        $this->_board->shiftOut($this->_dataPin, $this->_clockPin, [$address, $value]);
        for ($i = $this->_index + 1; $i < $this->_count; $i++) {
          $this->_board->shiftOut(
            $this->_dataPin, $this->_clockPin, [self::MODE_NOOP, self::MODE_NOOP]
          );
        }
      }
      $this->_board->digitalWrite($this->_latchPin, Firmata\Board::DIGITAL_HIGH);
    }

    /**
     * Activate the display
     * @return $this
     */
    public function on() {
      $this->transfer(self::MODE_POWER, self::VALUE_TRUE);
      return $this;
    }

    /**
     * Dectivate the display
     * @return $this
     */
    public function off() {
      $this->transfer(self::MODE_POWER, self::VALUE_FALSE);
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
      $this->transfer(self::MODE_INTENSITY, (int)$value);
      return $this;
    }

    /**
     * Set all segments off
     *
     * @return $this
     */
    public function clear() {
      for ($i = 1; $i <= 8; $i++) {
        $this->transfer($i, 0x00);
      }
      return $this;
    }
  }
}