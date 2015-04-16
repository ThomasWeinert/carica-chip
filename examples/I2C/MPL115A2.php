<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($loop, $board) {
      $sensor = new Carica\Chip\I2C\MPL115A2(
        $i2c = new Carica\Firmata\I2C($board)
      );
      
      $sensor
        ->read()
        ->done(
          function($pressure, $temperature) {
            echo "Pressure: ", number_format($pressure, 4), " kPa\n";
            echo "Temp: ", number_format($temperature, 1), " °C\n";
          }
        )
        ->fail(
          function($message) {
            echo 'ERROR: ', $message, "\n";
          }
        );
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

$loop->run();