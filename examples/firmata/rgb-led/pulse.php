<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Rgb\Led(
        $board->pins[FIRMATA_PINS['RGB_LED_RED']],
        $board->pins[FIRMATA_PINS['RGB_LED_GREEN']],
        $board->pins[FIRMATA_PINS['RGB_LED_BLUE']]
      );
      $led->color('#FC0')->pulse()->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();
