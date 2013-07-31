<?php
$board = require(__DIR__.'/bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Led($board, 9);
      $led->blink();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();