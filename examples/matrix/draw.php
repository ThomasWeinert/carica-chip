<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      $max = new Carica\Chip\Max7219\Matrix(
        $board,
        11,// white, data
        12, // blue, clock
        8 // green, latch
      );
      $displayCount = 1;
      for ($i = 0; $i < $displayCount; $i++) {
        $max->addDisplay($displayCount, 0);
      }
      $max->brightness(0)->on();
      $image = imagecreatefrompng(__DIR__.'/elephpant.png');
      $loop->setInterval(
        function() use ($max, $image) {
          static $offset = 0;
          $max->draw($image, $offset);
          if (--$offset < -15) {
            $offset = 0;
          }
        },
        200
      );
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();