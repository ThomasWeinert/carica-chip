<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      $max = new Carica\Chip\Max7219\Matrix\Display(
        $board,
        11,// white, data
        12, // blue, clock
        8 // green, latch
      );
      for ($i = 0; $i < 8; $i++) {
        $max[$i][7 - $i] = TRUE;
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