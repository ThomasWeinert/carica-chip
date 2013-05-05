<?php
require('../vendor/autoload.php');
require('../src/Carica/Chip/Led.php');

$board = new Carica\Io\Firmata\Board(
  new Carica\Io\Stream\Tcp('127.0.0.1', 5339)
);
$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Led($board, 13);
      $led->blink();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();