<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new Carica\Chip\Max7219\SegmentDisplay(
        new \Carica\Firmata\ShiftOut(
          $board->pins[8], // green, latch
          $board->pins[12], // blue, clock
          $board->pins[11] // white, data
        ),
        8
      );
      // show one led on each segment
      $leds = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'dp'];
      foreach ($leds as $index => $led) {
        $max[$index]->setSegment($led, TRUE);
      }
      $max->brightness(0)->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();