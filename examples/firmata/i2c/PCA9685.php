<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $pwmPins = new \Carica\Chip\I2C\PCA9685(
        new Carica\Firmata\I2C($board)
      );
      $led = new Carica\Chip\Led($pwmPins[0]);
      $led->pulse()->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();