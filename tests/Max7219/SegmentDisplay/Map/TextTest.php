<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {

  include_once(__DIR__ . '/../../../Bootstrap.php');

  use Carica\Chip\TestCase;


  class TextTest extends TestCase {

    /**
     * @covers Carica\Chip\Max7219\SegmentDisplay\Map\Text
     * @dataProvider provideTextAndBytes
     */
    public function testIterator($expectedBytes, $number, $decimals = 0) {
      $map = new Text($number, $decimals);
      $this->assertEquals(
        $expectedBytes, iterator_to_array($map)
      );
    }

    public static function provideTextAndBytes() {
      return [
        'PHP' => [
          [ 0x67, 0x37, 0x67 ],
          'PHP'
        ],
        'MOOOH' => [
          [ 0x37, 0x7e, 0x7e, 0x7e, 0x72, 0x66 ],
          'MOOOH'
        ],
        'Hello World!' => [
          [ 0xb0, 0x3d, 0xe, 0x66, 0x7e, 0x3c, 0x1e, 0x00, 0x7e, 0xe, 0xe, 0x4f, 0x37 ],
          'Hello World!'
        ]
      ];
    }
  }
}