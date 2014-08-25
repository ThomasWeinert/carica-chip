<?php
use Carica\Chip\Max7219\SegmentDisplay;
use Carica\Chip\Max7219\SegmentDisplay\Segment;

$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new SegmentDisplay(
        $board,
        11,// white, data
        12, // blue, clock
        8, // green, latch,
        8
      );
      $numbers = [
        0 => Segment::A | Segment::B | Segment::C | Segment::D | Segment::E | Segment::F,
        1 => Segment::B | Segment::C,
        2 => Segment::A | Segment::B | Segment::G | Segment::E | Segment::D,
        3 => Segment::A | Segment::B | Segment::G | Segment::C | Segment::D,
        4 => Segment::F | Segment::B | Segment::G | Segment::C,
        5 => Segment::A | Segment::F | Segment::G | Segment::C | Segment::D,
        6 => Segment::A | Segment::F | Segment::G | Segment::C | Segment::D | Segment::E,
        7 => Segment::A | Segment::B | Segment::C,
        8 => Segment::A | Segment::B | Segment::C | Segment::D | Segment::E | Segment::F | Segment::G,
        9 => Segment::A | Segment::B | Segment::G | Segment::C | Segment::D | Segment::F
      ];

      // show one led on each segment
      for ($i = 0; $i < 8; $i++) {
        $max[$i]->setValue($numbers[$i + 2]);
        echo 'Digit: ', ($i + 2), ' 0x', str_pad(dechex($max[$i]->getValue()), 2, '0', STR_PAD_LEFT), "\n";
      }
      $max->update()->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();