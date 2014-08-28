<?php

namespace Carica\Chip\Max7219\SegmentDisplay {

  use Carica\Firmata;
  use Carica\Io\Event\Emitter;

  /**
   *
   * @method onChange(callable $callback) attach a change callback
   * @method onceChange(callable $callback) attach a change callback that is executed once
   */
  class Segment {

    use Emitter\Aggregation;

    const A = 64;
    const B = 32;
    const C = 16;
    const D = 8;
    const E = 4;
    const F = 2;
    const G = 1;
    const DP = 128;

    /**
     * Name the segments
     *
     * @var array
     */
    private $_segments = [
      'a' => self::A,
      'b' => self::B,
      'c' => self::C,
      'd' => self::D,
      'e' => self::E,
      'f' => self::F,
      'g' => self::G,
      'dp' => self::DP,
      '.' => self::DP,
    ];

    private $_value = 0x00;

    /**
     * Allow to get segment by name or as value
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
      if ($this->isSegmentName($name)) {
        return $this->getSegment($name);
      }
      switch ($name) {
      case 'value' :
        return $this->getValue();
      }
      return NULL;
    }

    /**
     * Allow to set segment by name or as value
     *
     * @param string $name
     */
    public function __set($name, $value) {
      if ($this->isSegmentName($name)) {
        $this->setSegment($name, (bool)$value);
        return;
      }
      switch ($name) {
      case 'value' :
        $this->setValue($value);
        return;
      }
    }

    /**
     * Lazy create for the event emitter, defines the possible event.
     *
     * @return Emitter
     */
    protected function createEventEmitter() {
      $emitter = new Emitter;
      $emitter->defineEvents(
        array('change')
      );
      return $emitter;
    }

    /**
     * Check if the string is a valid segment name or
     * and return it as lowercase.
     *
     * @param $name
     * @return bool|string
     */
    private function isSegmentName($name) {
      $name = strtolower($name);
      return (isset($this->_segments[$name])) ? $name : FALSE;
    }

    /**
     * Get the byte value of the segment
     *
     * @return int
     */
    public function getValue() {
      return $this->_value;
    }

    /**
     * Set the byte value of the segment
     *
     * @param $value
     */
    public function setValue($value) {
      $value = (int)$value;
      if ($value < 0) {
        $value = 0;
      } elseif ($value > 255) {
        $value = 255;
      }
      if ($value !== $this->_value) {
        $this->_value = $value;
        $this->emitEvent('change', $this);
      }
    }

    /**
     * Get the status of a segment
     *
     * @param string $name
     * @return bool
     */
    public function getSegment($name) {
      if ($index = $this->isSegmentName($name)) {
        $bit = $this->_segments[$index];
        return ($this->_value & $bit) == $bit;
      }
      return FALSE;
    }

    /**
     * Set the status of a segment
     *
     * @param $name
     * @param $active
     */
    public function setSegment($name, $active) {
      if ($index = $this->isSegmentName($name)) {
        $bit = $this->_segments[$index];
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
  }
}