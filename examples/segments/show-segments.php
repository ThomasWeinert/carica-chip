<?php
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
      // show one led on each segment
      $leds = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'dp'];
      foreach ($leds as $index => $led) {
        $max[$index]->setSegment($led, TRUE);
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