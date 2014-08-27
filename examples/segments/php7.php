<?php
use Carica\Chip\Max7219\SegmentDisplay\Map\Pad;
use Carica\Chip\Max7219\SegmentDisplay\Map\Text;
use Carica\Chip\Max7219\SegmentDisplay\Segment;

$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new Carica\Chip\Max7219\SegmentDisplay(
        $board,
        11,// white, data
        12, // blue, clock
        8 // green, latch
      );
      $max
        ->brightness(0.4)
        ->show(
          new Pad(
            new Text('PHP7'), 8
          )
        )
        ->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();