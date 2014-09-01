<?php
use Carica\Chip\Max7219\SegmentDisplay;

$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new SegmentDisplay(
        $board,
        11,// white, data
        12, // blue, clock
        8 // green, latch
      );
      $max
        ->brightness(0.6)
        ->scroll(new SegmentDisplay\Map\Text('Einen Milchkaffee bitte. - '), 150)
        ->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();