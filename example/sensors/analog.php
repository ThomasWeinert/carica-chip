<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $sensor = new Carica\Chip\Sensor\Analog($board, 14);
      $sensor->onChange(
        function ($sensor) {
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