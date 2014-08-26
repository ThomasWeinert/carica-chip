<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {

  include_once(__DIR__ . '/../../../Bootstrap.php');

  use Carica\Chip\TestCase;


  class NumberTest extends TestCase {

    /**
     * @covers Carica\Chip\Max7219\SegmentDisplay\Map\Number
     * @dataProvider provideNumbersAndBytes
     */
    public function testIterator($expectedBytes, $number, $decimals = 0) {
      $map = new Number($number, $decimals);
      $this->assertEquals(
        $expectedBytes, iterator_to_array($map)
      );
    }

    public static function provideNumbersAndBytes() {
      return [
        '1' => [
          [ 0x30 ], 1
        ],
        '1.00' => [
          [ 0x7E, 0x7E, 0xB0 ], 1, 2
        ],
        '-2' => [
          [ 0x6d, 0x01 ], -2
        ],
      ];
    }
  }
}