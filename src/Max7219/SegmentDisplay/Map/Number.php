<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {


  class Number extends Text {

    public function __construct($number, $decimals = 0) {
      parent::__construct(number_format($number, $decimals, '.', ''));
    }

  }
}