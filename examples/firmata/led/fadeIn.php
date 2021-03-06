<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Led($board->pins[FIRMATA_PINS['PIN_LED']]);
      $led->brightness(0.5)->fadeIn(5000);
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();
