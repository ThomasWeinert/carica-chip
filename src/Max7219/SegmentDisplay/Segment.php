<?php

namespace Carica\Chip\Max7219\SegmentDisplay {

  use Carica\Firmata;

  class Segment {

    const SEGMENT_A = 64;
    const SEGMENT_B = 32;
    const SEGMENT_C = 16;
    const SEGMENT_D = 8;
    const SEGMENT_E = 4;
    const SEGMENT_F = 2;
    const SEGMENT_G = 1;
    const SEGMENT_DP = 128;

    private $_segments = [
      'a' => self::SEGMENT_A,
      'b' => self::SEGMENT_B,
      'c' => self::SEGMENT_C,
      'd' => self::SEGMENT_D,
      'e' => self::SEGMENT_E,
      'f' => self::SEGMENT_F,
      'g' => self::SEGMENT_G,
      'dp' => self::SEGMENT_DP,
      '.' => self::SEGMENT_DP,
    ];

    private $_value = 0x00;

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

    private function isSegmentName($name) {
      $name = strtolower($name);
      return (isset($this->_segments[$name])) ? $name : FALSE;
    }

    public function getValue() {
      return $this->_value;
    }

    public function setValue($value) {
      $value = (int)$value;
      if ($value < 0) {
        $this->_value = 0;
      } elseif ($value > 255) {
        $this->_value = 255;
      } else {
        $this->_value = $value;
      }
    }

    public function getSegment($name) {
      if ($index = $this->isSegmentName($name)) {
        $bit = $this->_segments[$index];
        return ($this->_value & $bit) == $bit;
      }
      return FALSE;
    }

    public function setSegment($name, $active) {
      if ($index = $this->isSegmentName($name)) {
        $bit = $this->_segments[$index];
        if ($active) {
          $this->_value |= $bit;
        } else {
          $this->_value &= ~$bit;
        }
      }
    }
  }
}