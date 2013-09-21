<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $sensor = new Carica\Chip\Sensor\Analog(
        $board->pins[16]
      );
      $sensor->onChange(
        function (Carica\Chip\Sensor\Analog $sensor) {
          echo $sensor, "\n";
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