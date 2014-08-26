<?php
use Carica\Chip\Max7219\SegmentDisplay\Map\Text;

$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new Carica\Chip\Max7219\SegmentDisplay(
        $board,
        11,// white, data
        12, // blue, clock
        8, // green, latch,
        8
      );
      $max
        ->brightness(0.6)
        ->scroll(new Text('HELLO FROM PHP - '), 150)
        ->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();