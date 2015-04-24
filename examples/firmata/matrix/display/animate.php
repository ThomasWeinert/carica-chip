<?php
$board = require(__DIR__.'/../../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      $max = new Carica\Chip\Max7219\Matrix\Display(
        new \Carica\Firmata\ShiftOut(
          $board->pins[8], // green, latch
          $board->pins[12], // blue, clock
          $board->pins[11] // white, data
        )
      );
      $loop->setInterval(
        function () use ($max) {
          static $i = 0;
          $max->setRow($i, FALSE, FALSE);
          if (++$i > 7) {
            $i = 0;
          }
          $max->setRow($i, TRUE, TRUE);
        },
        500
      );
      $max->brightness(0)->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();