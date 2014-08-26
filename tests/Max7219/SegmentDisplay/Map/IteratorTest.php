<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {

  include_once(__DIR__ . '/../../../Bootstrap.php');

  use Carica\Chip\Max7219\SegmentDisplay\Segment;
  use Carica\Chip\TestCase;


  class IteratorTest extends TestCase {

    /**
     * @covers Carica\Chip\Max7219\SegmentDisplay\Map\Iterator
     * @dataProvider provideTextAndBytes
     */
    public function testIterator($expectedBytes, $text) {
      $iterator = new Iterator(
        $text,
        [
          '-' => Segment::G,
          ' ' => 0x00,
          '_' => [Segment::D, Segment::D]
        ]
      );
      $this->assertEquals(
        $expectedBytes, iterator_to_array($iterator)
      );
    }

    public static function provideTextAndBytes() {
      return [
        [
          [0x01],
          '-'
        ],
        [
          [Segment::DP | 0x01],
          '-.'
        ],
        [
          [Segment::DP | 0x01, 0x01],
          '--.'
        ],
        [
          [Segment::DP, 0x01],
          '- .'
        ],
        [
          [0x01, Segment::DP | 0x01, 0x01],
          '--....-'
        ],
        [
          [0x08, 0x08],
          '_'
        ],
        [
          [0x08, 0x08 | Segment::DP],
          '_.'
        ]
      ];
    }
  }
}