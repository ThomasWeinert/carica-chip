<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {

  use Carica\Chip\Max7219\SegmentDisplay\Map;

  class Text implements Map {


    private $_map =[
      '-' => 0x01,
      '_' => 0x08,
      ' ' => 0x00,
      '0' => 0x7E,
      '1' => 0x30,
      '2' => 0x6D,
      '3' => 0x79,
      '4' => 0x33,
      '5' => 0x5B,
      '6' => 0x5F,
      '7' => 0x70,
      '8' => 0x7F,
      '9' => 0x7B,
      'A' => 0x77,
      'B' => 0x1F,
      'C' => 0x4E,
      'D' => 0x3D,
      'E' => 0x4F,
      'F' => 0x47,
      'G' => 0x5E,
      'H' => 0x37,
      'I' => 0x06,
      'J' => 0x38,
      'K' => [0x07, 0x4E],
      'L' => 0x0E,
      'M' => [0x66, 0x72],
      'N' => 0x76,
      'O' => 0x7E,
      'P' => 0x67,
      'Q' => 0x6B,
      'R' => 0x66,
      'S' => 0x5B,
      'T' => 0x0F,
      'U' => 0x3E,
      'V' => 0x1C,
      'W' => [0x1E, 0x3C],
      'X' => 0x37,
      'Y' => 0x33,
      'Z' => 0x6D,
      '°' => 0x63,
      '^' => 0x62,
      '!' => 0xb0,
      '\'' => 0x20,
      '"' => 0x22
     ];

    private $_text = '';

    public function __construct($text) {
      $this->_text = strtoupper($text);
    }

    public function getIterator() {
      return new Iterator($this->_text, $this->_map);
    }

  }
}