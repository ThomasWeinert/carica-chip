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
      // show one led on each segment
      $bytes = new Text('PHP "7"');
      foreach ($bytes as $index => $byte) {
        if (isset($max[$index])) {
          $max[$index]->setValue($byte);
        }
      }
      $max->brightness(1)->update()->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();