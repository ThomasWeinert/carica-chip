<?php
use Carica\Chip\Max7219\SegmentDisplay;

$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new SegmentDisplay(
        new \Carica\Firmata\ShiftOut(
          $board->pins[8], // green, latch
          $board->pins[12], // blue, clock
          $board->pins[11] // white, data
        )
      );
      $max
        ->brightness(0.6)
        ->scroll(new SegmentDisplay\Map\Text('Hello from PHP - '), 150)
        ->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();