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
          function() {
            var_dump(func_get_args());
          }
        )
        ->fail(
          function($message) {
            var_dump('ERROR', $message);
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