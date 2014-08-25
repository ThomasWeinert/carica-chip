<?php

namespace Carica\Chip\Max7219\SegmentDisplay {

  use Carica\Firmata;

  class Segment {

    const A = 64;
    const B = 32;
    const C = 16;
    const D = 8;
    const E = 4;
    const F = 2;
    const G = 1;
    const DP = 128;

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