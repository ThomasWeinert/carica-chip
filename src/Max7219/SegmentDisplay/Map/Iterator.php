<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {

  use Carica\Chip\Max7219\SegmentDisplay\Segment;

  class Iterator extends \ArrayIterator {

    public function __construct($string, array $map) {
      $bytes = [];
      $length = strlen($string);
      $decimalPoint = FALSE;
      $offset = $length;
      while ($offset > 0) {
        $char = substr($string, --$offset, 1);
        if ($char === '.') {
          $decimalPoint = TRUE;
        } elseif ($char !== '' && isset($map[$char])) {
          $buffer = is_array($map[$char]) ? array_reverse($map[$char]) : [$map[$char]];
          if ($decimalPoint) {
            $buffer[count($buffer) - 1] |= Segment::DP;
          }
          $decimalPoint = FALSE;
          foreach ($buffer as $byte) {
            $bytes[] = $byte;
          }
        }
      }
      parent::__construct($bytes);
    }
  }
}