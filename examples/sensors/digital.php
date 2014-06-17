<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $sensor = new Carica\Chip\Sensor\Digital(
        $board->pins[2]
      );
      $sensor->onChange(
        function (Carica\Chip\Sensor\Digital $sensor) {
          echo $sensor->isHigh()
            ? 'Key down  ______________' : 'Key up    |""""""""""""|',
            "\n\n";
        }
      );
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();