<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $max = new Carica\Chip\Max7219\MatrixDisplay(
        $board,
        11,// white, data
        12, // blue, clock
        8, // green, latch,
        8
      );
      for ($i = 0; $i < 8; $i++) {
        $max[$i][$i] = TRUE;
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