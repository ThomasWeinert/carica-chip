<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($loop, $board) {
      $dac = new Carica\Chip\I2C\MCP4725(
        $i2c = new Carica\Firmata\I2C($board)
      );
      $steps = 5;
      $voltage = 3.3;
      
      /*
       * Enable debug to output the bytes on the console.
       * They will not be send to to device if debug is enabled.
       * 
       * Example: "write 0x62: 00001111 11111111"
       */ 
      $i2c->debug(FALSE);
      $i2c->events()->on(
        'debug',
        function($method, $address, $binary) {
          echo $method, ' ', $address, ': ', $binary, "\n";
        }
      );
      
      $loop->setInterval(
        function() use ($dac, $steps, $voltage) {
          static $counter = 0;
          $analog = $counter / ($steps - 1);
          var_dump($analog * $voltage);
          $dac->setAnalog($analog);
          if (++$counter >= $steps) {
            $counter = 0;
          }
        },
        2000
      );
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

$loop->run();