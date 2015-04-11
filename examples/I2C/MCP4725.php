<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($loop, $board) {
      $dac = new Carica\Chip\I2C\MCP4725($board);
      $loop->setInterval(
        function() use ($dac) {
          static $counter = 1;
          $max = 5;
          $analog = $counter / $max;
          var_dump($analog * 3.3);
          $dac->setValue($analog);
          if (++$counter > $max) {
            $counter = 1;
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