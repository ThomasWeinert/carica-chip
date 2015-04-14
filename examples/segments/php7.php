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
        ->brightness(0.4)
        ->show(
          new SegmentDisplay\Map\Pad(
            new SegmentDisplay\Map\Text('PHP7'), 8
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