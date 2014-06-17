<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Rgb\Led(
        $board->pins[3],
        $board->pins[5],
        $board->pins[6]
      );
      $led->color('#FC0')->strobe()->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();