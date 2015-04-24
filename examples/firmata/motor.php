<?php
$board = require(__DIR__.'/bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      $motorLeft = new Carica\Chip\Motor($board->pins[9], $board->pins[2], $board->pins[3]);
      $motorRight = new Carica\Chip\Motor($board->pins[10], $board->pins[7], $board->pins[8]);
      $motorLeft->forward(1);
      $motorRight->forward(1);
      $loop->setTimeout(
        function () use ($motorLeft, $motorRight) {
          $motorLeft->backward(1);
          $motorRight->backward(1);
        },
        5000
      );
      $loop->setTimeout(
        function () use ($motorLeft, $motorRight) {
          $motorLeft->stop();
          $motorRight->stop();
        },
        10000
      );
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();