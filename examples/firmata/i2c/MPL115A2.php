<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($loop, $board) {
      $sensor = new Carica\Chip\I2C\MPL115A2(
        $i2c = new Carica\Firmata\I2C($board, \Carica\Chip\I2C\MPL115A2::ADDRESS)
      );
      
      $sensor
        ->read()
        ->done(
          function($pressure, $temperature) {
            echo "Pressure: ", number_format($pressure, 4), " kPa\n";
            echo "Temperature: ", number_format($temperature, 1), " degC\n";
          }
        )
        ->fail(
          function($message) {
            echo 'ERROR: ', $message, "\n";
          }
        );
      
      $sensor
        ->getPressure()
        ->done(
          function($pressure) {
            echo "Pressure: ", number_format($pressure, 4), " kPa\n";
          }
        );
      
      $sensor
        ->getTemperature()
        ->done(
          function($temperature) {
            echo "Temperature: ", number_format($temperature, 1), " degC\n";
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