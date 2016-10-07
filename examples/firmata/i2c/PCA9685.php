<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $pwmPins = new \Carica\Chip\I2C\PCA9685(
        $i2c = new Carica\Firmata\I2C($board, \Carica\Chip\I2C\PCA9685::ADDRESS)
      );
      $i2c->debug(FALSE);
      $i2c->events()->on(
        'debug',
        function($method, $address, $binary) {
          echo $method, ' ', $address, ': ', $binary, "\n";
        }
      );
      $led = new Carica\Chip\Led($pwmPins[1]);
      $led->pulse()->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();